<?php

/**
 * The <code>Prototype</code> class represents objects that hava properties dinamicly modified.
 * @author Tasso Evangelista
 */
class Prototype implements \ArrayAccess, \Serializable
{
	private static $callablePool = array();

	public static function store($callable)
	{
		self::$callablePool[] = $callable;
		return count(self::$callablePool) - 1;
	}

	public static function restore($idx)
	{
		$callable = self::$callablePool[$idx];
		unset(self::$callablePool[$idx]);
		return $callable;
	}

	private $properties = array();

	public function offsetExists($property)
	{
		return isset($this->properties[$property]);
	}

	public function offsetGet($property)
	{
		return $this->offsetExists($property) ? $this->properties[$property] : null;
	}

	public function offsetSet($property, $value)
	{
		$this->properties[$property] = $value;
	}

	public function offsetUnset($property)
	{
		unset($this->properties[$property]);
	}

	public function __isset($property)
	{
		return $this->offsetExists($property);
	}

	public function __get($property)
	{
		return $this->offsetGet($property);
	}

	public function __set($property, $value)
	{
		$this->offsetSet($property, $value);
	}

	public function __unset($property)
	{
		$this->offsetUnset($property);
	}

	public function __call($property, array $args)
	{
		$callable = $this->offsetGet($property);
		array_unshift($args, $this);

		if ( $callable instanceof Closure )
			return call_user_func_array($callable, $args);
		else
			throw new BadMethodCallException(sprintf('%s is not callable', $property));
	}

	public function serialize()
	{
		$properties = array();
		foreach ( $this->properties as $name => $value ) {
			if ( $value instanceof Closure ) {
				$idx = self::store($value);
				$properties['callable_' . $name] = $idx;
			}
			else
				$properties['mixed_' . $name] = serialize($value);
		}

		return serialize($properties);
	}

	public function unserialize($serialized)
	{
		$properties = unserialize($serialized);
		$this->properties = array();

		foreach ( $properties as $name => $value ) {
			if ( substr($name, 0, strlen('callable_')) == 'callable_' )
				$this->properties[substr($name, strlen('callable_'))] = self::restore($value);
			elseif ( substr($name, 0, strlen('mixed_')) == 'mixed_' )
				$this->properties[substr($name, strlen('mixed_'))] = unserialize($value);
		}
	}

}