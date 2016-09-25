<?php
use TassoEvan\Prototype\Prototype;

class PrototypeTest extends PHPUnit_Framework_TestCase
{
	private function _expectException($exceptionClass, \Closure $test) {
		$ok = false;
		try {
			$test($this);
			$ok = true;
		}
		catch ( Exception $e ) {
			if ( !is_a($e, $exceptionClass) )
				$this->fail("The expected $exceptionClass has not been raised: " . get_class($e) . " as thrown.");
		}

		if ( $ok )
			$this->fail("The expected $exceptionClass has not been raised.");
	}

	public function testClosure()
	{
		$closure = Prototype::closure('strtolower');
		$this->assertEquals('john doe', $closure('John Doe'));
	}

	public function testInvoke()
	{
		$uniqid = uniqid();

		$obj = new Prototype(function() use ($uniqid) {
			return $uniqid;
		});

		$this->assertEquals($uniqid, $obj());
	}

	public function testInvokeWithThisReference()
	{
		$obj = new Prototype(function () use (&$obj) {
			return $this === $obj;
		});

		$this->assertTrue($obj());

		$obj = new Prototype(function () {
			$this->a = true;
			$this->b = Prototype::dynamic(function () {
				return false;
			});
		});

		$obj();

		$this->assertTrue($obj->a);
		$this->assertFalse($obj->b);
	}

	public function testInvokeWithNonClosures()
	{
		$obj = new Prototype('strpos');

		$this->assertEquals($obj('Prototype', 'type'), 5);
	}

	public function testInvokeNonInvokablePrototype()
	{
		$this->setExpectedException(BadMethodCallException::class);

		$obj = new Prototype();
		$obj();
	}

	public function testData()
	{
		$obj = new Prototype();

		$obj->a = 1;
		$obj->b = 'John';
		$obj->c = true;

		$obj->d = Prototype::dynamic(function() {
			return 'Doe';
		});

		$this->assertEquals(array('a' => 1, 'b' => 'John', 'c' => true, 'd' => 'Doe'), Prototype::data($obj));
	}

	public function testNormalProperty()
	{
		$obj = new Prototype();

		$obj->a = 1;
		$this->assertEquals(1, $obj->a);

		$obj->a = 2;
		$this->assertEquals(2, $obj->a);

		$obj->a = Prototype::normal(4);
		$this->assertEquals(4, $obj->a);
	}

	public function testReadOnlyProperty()
	{
		$obj = new Prototype();

		$obj->a = Prototype::readOnly(1);
		$this->assertEquals(1, $obj->a);

		$obj->a = 2;
		$this->assertEquals(1, $obj->a);
	}

	public function testReadOnlyStrictProperty()
	{
		$this->setExpectedException(UnexpectedValueException::class);

		$obj = new Prototype();

		$obj->a = Prototype::readOnly(1, true);
		$this->assertEquals(1, $obj->a);

		$obj->a = 2;
	}

	public function testLazyProperty()
	{
		$obj = new Prototype();

		$obj->a = Prototype::lazy(function () {
			return rand();
		});

		$this->assertEquals($obj->a, $obj->a);

		$obj->a = Prototype::lazy(function () use ($obj) {
			return $this === $obj;
		});

		$this->assertTrue($obj->a);

		$obj->a = Prototype::lazy('phpversion');

		$this->assertEquals($obj->a, phpversion());
	}

	public function testProxyProperty()
	{
		$obj = new Prototype();

		$obj->a = Prototype::proxy(function ($property) {
			return $property->get();
		}, function ($property, $newValue) {
			return $property->set($newValue);
		}, Prototype::normal(1));

		$this->assertEquals(1, $obj->a);
		$obj->a = 2;
		$this->assertEquals(2, $obj->a);

		$obj->a = Prototype::proxy(function ($property) {
			return $property->get();
		}, function ($property, $newValue) {
			return $property->set($newValue);
		}, 1);

		$this->assertEquals(1, $obj->a);
		$obj->a = 2;
		$this->assertEquals(2, $obj->a);

		$obj->a = 2;
		$obj->b = Prototype::proxy(function ($property) {
			return $this->a * $property->get();
		}, function ($property, $newValue) {
			return $property->set($newValue);
		}, 1);

		$this->assertEquals(2, $obj->b);
		$obj->b = 3;
		$this->assertEquals(6, $obj->b);
		$obj->a = 3;
		$this->assertEquals(9, $obj->b);

		$obj->a = 2;
		$obj->b = Prototype::proxy(function ($property) {
			return $property->get();
		}, function ($property, $newValue) {
			return $property->set($newValue - $this->a);
		}, 1);

		$this->assertEquals(1, $obj->b);
		$obj->b = 1;
		$this->assertEquals(-1, $obj->b);
	}

	public function testDynamicProperty()
	{
		$obj = new Prototype();

		$value = 'World';

		$obj->a = Prototype::dynamic(function () use (&$value) {
			return "Hello, {$value}!";
		}, function ($newValue) use (&$value) {
			return $value = $newValue;
		});

		$this->assertEquals('Hello, World!', $obj->a);
		$obj->a = 'Prototype';
		$this->assertEquals('Hello, Prototype!', $obj->a);
		$value = 'hacker';
		$this->assertEquals('Hello, hacker!', $obj->a);
	}

	public function testUncallablePropertyInvoked()
	{
		$this->setExpectedException(BadMethodCallException::class);

		$obj = new Prototype();

		$obj->a = 1;
		$obj->a();
	}
}
