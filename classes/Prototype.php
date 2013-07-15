<?php
/**
* Prototype - Simple prototype-based programming in PHP
*
* @author Tasso Evangelista <tassoevan@tassoevan.me>
* @copyright 2013 Tasso Evangelista
* @link http://github.com/tassoevan/prototype
* @license http://github.com/tassoevan/prototype/LICENSE
* @version 1.1
* @package Prototype
*
* MIT LICENSE
*
* Permission is hereby granted, free of charge, to any person obtaining
* a copy of this software and associated documentation files (the
* "Software"), to deal in the Software without restriction, including
* without limitation the rights to use, copy, modify, merge, publish,
* distribute, sublicense, and/or sell copies of the Software, and to
* permit persons to whom the Software is furnished to do so, subject to
* the following conditions:
*
* The above copyright notice and this permission notice shall be
* included in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
* EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
* MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
* NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
* LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
* OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
* WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

/**
 * The <code>Prototype</code> class represents objects that hava properties dinamicly modified.
 * @author Tasso Evangelista
 */
class Prototype implements \ArrayAccess, \Serializable, \IteratorAggregate
{
	private static $callablePool = array();

	/**
	 * Stores a callable value in pool, for serialization.
	 * @param callable $callable
	 * @return integer the index that represents the callable in pool
	 */
	public static function store($callable)
	{
		self::$callablePool[] = $callable;
		return count(self::$callablePool) - 1;
	}

	/**
	 * Restores a callable value in pool, for serialization.
	 * @param integer $idx the index that represents the callable in pool
	 * @return callable the referenced callable
	 */
	public static function restore($idx)
	{
		$callable = self::$callablePool[$idx];
		unset(self::$callablePool[$idx]);
		return $callable;
	}

	private $callable;
	private $properties = array();

	/**
	 * Constructs a new Prototype instance. If a callable is provided, the own prototype becomes callable via __invoke() method.
	 * @param callable|null $callable the callable that may be invoked
	 */
	public function __construct($callable = null)
	{
		$this->callable = $callable;
	}

	/**
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists($property)
	{
		return isset($this->properties[$property]);
	}

	/**
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet($property)
	{
		return $this->offsetExists($property) ? $this->properties[$property] : null;
	}

	/**
	 * @see ArrayAccess::offsetSet()
	 */
	public function offsetSet($property, $value)
	{
		$this->properties[$property] = $value;
	}

	/**
	 * @see ArrayAccess::offsetUnset()
	 */
	public function offsetUnset($property)
	{
		unset($this->properties[$property]);
	}

	/**
	 * @see __isset()
	 */
	public function __isset($property)
	{
		return $this->offsetExists($property);
	}

	/**
	 * @see __get()
	 */
	public function __get($property)
	{
		return $this->offsetGet($property);
	}

	/**
	 * @see __set()
	 */
	public function __set($property, $value)
	{
		$this->offsetSet($property, $value);
	}

	/**
	 * @see __unset()
	 */
	public function __unset($property)
	{
		$this->offsetUnset($property);
	}

	/**
	 * @see __call()
	 */
	public function __call($property, array $args)
	{
		$callable = $this->offsetGet($property);

		if ( is_callable($callable) )
			return call_user_func_array($callable, $args);
		else
			throw new BadMethodCallException(sprintf('%s is not callable', $property));
	}

	/**
	 * @see __invoke()
	 */
	public function __invoke()
	{
		if ( is_callable($this->callable) )
			return call_user_func_array($this->callable, func_get_args());
		else
			throw new BadMethodCallException('Prototype is not callable');
	}

	/**
	 * @see Serializable::serialize()
	 */
	public function serialize()
	{
		$properties = array();

		$idx = self::store($this->callable);
		$properties['callable_#'] = $idx;

		foreach ( $this->properties as $name => $value ) {
			if ( is_callable($value) ) {
				$idx = self::store($value);
				$properties['callable_' . $name] = $idx;
			}
			else
				$properties['mixed_' . $name] = serialize($value);
		}

		return serialize($properties);
	}

	/**
	 * @see Serializable::unserialize()
	 */
	public function unserialize($serialized)
	{
		$properties = unserialize($serialized);
		$this->properties = array();

		foreach ( $properties as $name => $value ) {
			if ( substr($name, 0, strlen('callable_')) == 'callable_' ) {
				if ( $name == 'callable_#' )
					$this->callable = self::restore($value);
				else
					$this->properties[substr($name, strlen('callable_'))] = self::restore($value);
			}
			elseif ( substr($name, 0, strlen('mixed_')) == 'mixed_' )
				$this->properties[substr($name, strlen('mixed_'))] = unserialize($value);
		}
	}

	/**
	 * @see IteratorAggregate::getIterator()
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this);
	}

}