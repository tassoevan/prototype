<?php
class PrototypeTest extends PHPUnit_Framework_TestCase
{
	
	public function testUse()
	{
		$obj = new Prototype();
		$obj->a = 'Test';
		$obj->b = 12;
		$obj->c = function() use($obj) {
			static $j;
			return $obj->a . ' ' . $obj->b;
		};

		$this->assertEquals('Test 12', $obj->c());
		unset($obj->a);
		$this->assertEquals(null, $obj->a);
		$this->assertFalse(isset($obj->a));
		$this->assertTrue(isset($obj->b));

		$obj = new Prototype(function() {
			return 'invoked as function';
		});

		$this->assertEquals('invoked as function', $obj());

		$this->assertInstanceOf('Iterator', $obj->getIterator());

		$obj = new Prototype(Prototype::closure('strtoupper'));
		$this->assertEquals('TEST', $obj('test'));
	}

	public function testInvalidCall()
	{
		$this->setExpectedException('BadMethodCallException');

		$obj = new Prototype();
		$obj->a = 'Test';
		$obj->a();
	}

	public function testInvalidInvoke()
	{
		$this->setExpectedException('BadMethodCallException');

		$obj = new Prototype();
		$obj();
	}

	public function testSerialization()
	{
		$obj = new Prototype();
		$obj->a = 'Test';
		$obj->b = 12;
		$obj->c = function(Prototype $that) {
			return $that->a . ' ' . $that->b;
		};

		$unserialized = unserialize(serialize($obj));

		$this->assertEquals($obj, $unserialized);
	}

}