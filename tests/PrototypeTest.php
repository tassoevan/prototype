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

	public function testNormal()
	{
		$obj = new Prototype();

		// definitions
		$obj['a'] = 1;
		$obj->b = 2;
		$obj['c'] = Prototype::normal(3);
		$obj->d = Prototype::normal(4);

		// isset
		$this->assertTrue(isset($obj['a']));
		$this->assertTrue(isset($obj->a));
		$this->assertTrue(isset($obj['b']));
		$this->assertTrue(isset($obj->b));
		$this->assertTrue(isset($obj['c']));
		$this->assertTrue(isset($obj->c));
		$this->assertTrue(isset($obj['d']));
		$this->assertTrue(isset($obj->d));

		// get
		$this->assertEquals(1, $obj['a']);
		$this->assertEquals(1, $obj->a);
		$this->assertEquals(2, $obj['b']);
		$this->assertEquals(2, $obj->b);
		$this->assertEquals(3, $obj['c']);
		$this->assertEquals(3, $obj->c);
		$this->assertEquals(4, $obj['d']);
		$this->assertEquals(4, $obj->d);

		// unset
		unset($obj['a']);
		$this->assertFalse(isset($obj['a']));
		unset($obj->b);
		$this->assertFalse(isset($obj['b']));
		unset($obj['c']);
		$this->assertFalse(isset($obj->c));
		unset($obj->d);
		$this->assertFalse(isset($obj->d));

		// set
		$obj->a = 1;
		$obj->a = 2;
		$this->assertEquals(2, $obj->a);

		// call
		$obj->a = function($name) {
			return "Hello, $name";
		};
		$this->assertEquals('Hello, John Doe', $obj->a('John Doe'));

		// invalid call
		$this->_expectException('BadMethodCallException', function() use($obj) {
			$obj->a = 1;
			$obj->a();
			exit;
		});
	}

	public function testDynamic()
	{
		$obj = new Prototype();

		// definitions
		$obj->a = Prototype::dynamic(function() {
			static $counter = 1;

			return $counter++;
		});
		$obj->b = Prototype::dynamic(null);
		$obj->c = Prototype::dynamic(function() use($obj) {
			return $obj->a;
		}, function($value) use($obj) {
			unset($obj->a);
			$obj->a = $value;
		});
		$obj->d = Prototype::dynamic(null, null, function($name) {
			return "Hello, $name";
		});
		$obj->e = Prototype::dynamic(null, null, function() {
			static $i = 1;
			return $i++;
		});

		// get
		$this->assertEquals(1, $obj->a);
		$this->assertEquals(2, $obj->a);
		$this->assertNull($obj->b);

		// set
		$obj->a = 1;
		$this->assertEquals(3, $obj->a);

		$this->assertEquals(4, $obj->c);
		$obj->c = 1;
		$this->assertEquals(1, $obj->c);
		$this->assertEquals(1, $obj->c);

		// call
		$obj->d = 1;
		$this->assertEquals('Hello, John Doe', $obj->d('John Doe'));
		$this->assertNotEquals($obj->e(), $obj->e());

		$this->_expectException('BadMethodCallException', function() use($obj) {
			$obj->b();
		});
	}

	public function testLazy()
	{
		$obj = new Prototype();

		// definitions
		$obj->a = 1;
		$obj->b = Prototype::lazy(function() use($obj) {
			$obj->a = 2;
			return 1;
		});
		$obj->c = Prototype::lazy(function() {
			return rand();
		});

		// get
		$this->assertEquals(1, $obj->a);
		$this->assertEquals(1, $obj->b);
		$this->assertEquals(2, $obj->a);
		$obj->a = 1;
		$this->assertEquals(1, $obj->b);
		$this->assertEquals(1, $obj->a);

		$this->assertEquals($obj->c, $obj->c);

		// set
		$obj->b = 1;
		$this->assertEquals(1, $obj->a);
		$this->assertEquals(1, $obj->b);

		// call
		$obj->d = Prototype::lazy(function() {
			static $i = 0;
			++$i;

			return function() use($i) {
				return $i;
			};
		});

		$this->assertEquals($obj->d(), $obj->d());

		$this->_expectException('BadMethodCallException', function() use($obj) {
			$obj->b();
		});
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
}