<?php
class PrototypeTest extends PHPUnit_Framework_TestCase
{
	
	public function testUse()
	{
		$obj = new Prototype();
		$obj->a = 'Test';
		$obj->b = 12;
		$obj->c = function(Prototype $that) {
			return $that->a . ' ' . $that->b;
		};

		$this->assertEquals('Test 12', $obj->c());
		unset($obj->a);
		$this->assertEquals(null, $obj->a);
		$this->assertFalse(isset($obj->a));
		$this->assertTrue(isset($obj->b));
	}

	public function testInvalidCall()
	{
		$this->setExpectedException('BadMethodCallException');

		$obj = new Prototype();
		$obj->a = 'Test';
		$obj->a();
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