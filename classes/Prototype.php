<?php
/**
* Prototype - Simple prototype-based programming in PHP
*
* @author Tasso Evangelista <tassoevan@tassoevan.me>
* @copyright 2013 Tasso Evangelista
* @link http://github.com/tassoevan/prototype
* @license http://github.com/tassoevan/prototype/LICENSE
* @version 1.2.0
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
	private static function findToken(array $tokens, $needle, $start = 0, $end = PHP_INT_MAX)
	{
		$idx = false;
		for ( $i = $start, $end = min($end, count($tokens)); $i < $end; ++$i ) {
			if ( (is_int($needle) && is_array($tokens[$i]) && $tokens[$i][0] === $needle) ||
				is_string($needle) && is_string($tokens[$i]) && $tokens[$i] === $needle ) {
				$idx = $i;
				break;
			}
		}

		return $idx;
	}

	private static function getNesting(&$matches, array $tokens, $begin = '{', $end = '}', $offset = 0)
	{
		$level = 0;
		$start = false;

		for ( $i = $offset, $count = count($tokens); $i < $count; ++$i ) {
			if ( is_array($tokens[$i]) ? ($tokens[$i][0] === $begin) : ($tokens[$i] === $begin) ) {
				++$level;
				if ( $start === false )
					$start = $i + 1;
			}
			elseif ( is_array($tokens[$i]) ? ($tokens[$i][0] === $end) : ($tokens[$i] === $end) ) {
				if ( $start === false )
					break;

				if ( --$level == 0 ) {
					$matches = array(
						'tokens' => array_slice($tokens, $start, $i - $start),
						'start' => $start,
						'end' => $i - 1
					);
					return true;
				}
			}
		}

		$matches = null;
		return false;
	}

	/**
	 * Serializes a closure as string
	 * @param \Closure $closure
	 * @return string
	 */
	public static function serializeClosure(\Closure $closure) {
		$ref = new ReflectionFunction($closure);
		$tokens = token_get_all(file_get_contents($ref->getFileName()));

		$tokensCount = count($tokens);
		$start = false;
		$end = $tokensCount;

		for ( $i = 0; $i < $tokensCount; ++$i ) {
			if ( is_array($tokens[$i]) && $tokens[$i][0] === T_FUNCTION && $tokens[$i][2] == $ref->getStartLine() ) {
				$start = $i;
				break;
			}
		}

		for ( $i = $start; $i < $tokensCount; ++$i ) {
			if ( is_array($tokens[$i]) && $tokens[$i][2] > $ref->getEndLine() ) {
				$end = $i - 1;
				break;
			}
		}

		$tokens = array_slice($tokens, $start, $end);

		$replaceToken = function($a) { return is_array($a) ? $a[1] : $a; };
		
		while ( count($tokens) > 0 && $tokens[0][0] === T_FUNCTION ) {

			if ( !self::getNesting($parameters, $tokens, '(', ')') ) // does not have parameters
				die('fuck');

			if ( !self::getNesting($body, $tokens, '{', '}', $parameters['end']) ) // does not have body
				break;

			if ( ($use_idx = self::findToken($tokens, T_USE, $parameters['end'] + 2, $body['start'] - 2)) !== false )
				self::getNesting($use, $tokens, '(', ')', $use_idx);

			if ( self::findToken($tokens, T_STRING, 0, $parameters['start']) === false ) { // is anonymous function
				while ( self::getNesting($tmp, $body['tokens'], T_STATIC, ';') ) {
					$tmp['start']--;
					$tmp['end']++;
					array_splice($body['tokens'], $tmp['start'], $tmp['end']);
				}

				if ( !isset($use) )
					$use = array();

				$closure = compact('parameters', 'use', 'body');

				$variables = $ref->getStaticVariables();

				$source = "return function(" . implode('', array_map($replaceToken, $closure['parameters']['tokens'])) .  ") ";
				if ( !empty($closure['use']['tokens']) ) {
					$useParams = array_map('trim', explode(',', implode('', array_map($replaceToken, $closure['use']['tokens']))));

					foreach ( $useParams as $param ) {
						if ( $param[0] == '$')
							$source = "$param = " . var_export($variables[substr($param, 1)], true) . ";\n$source";
					}

					$source .= "use(" . implode(', ', $useParams) .  ") ";
				}
					
				$source .= "{" .  implode('', array_map($replaceToken, $closure['body']['tokens'])) . "};";

				$test = function($ref) use($source) {
					$newClosure = eval($source);
					$newRef = new ReflectionFunction($newClosure);

					return ( array_map(function($a) { return $a->getName(); }, $newRef->getParameters()) == array_map(function($a) { return $a->getName(); }, $newRef->getParameters()) );
				};

				if ( $test($ref) )
					return $source;
			}
			
			$function_idx = self::findToken($tokens, T_FUNCTION, $body['end'] + 2);

			$tokens = $function_idx === false ? array() : array_slice($tokens, $function_idx);
		}

		return null;
	}

	/**
	 * Constructs a closure for a generic callable
	 * @param callable $callable
	 * @return Closure
	 */
	public static function closure($callable)
	{
		return function() use($callable) {
			return call_user_func_array($callable, func_get_args());
		};
	}

	private $invokable;
	private $properties = array();

	/**
	 * Constructs a new Prototype instance. If a closure is provided, the own prototype becomes callable via __invoke() method.
	 * @param Closure|null $invokable the closure that may be invoked
	 */
	public function __construct(\Closure $invokable = null)
	{
		$this->invokable = $invokable;
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
		if ( $property === '#' )
			throw new \LogicException("invalid property name: '#'");

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
		$closure = $this->offsetGet($property);

		if ( $closure instanceof \Closure )
			return call_user_func_array($closure, $args);
		else
			throw new BadMethodCallException(sprintf('%s is not a closure', $property));
	}

	/**
	 * @see __invoke()
	 */
	public function __invoke()
	{
		if ( $this->invokable instanceof \Closure )
			return call_user_func_array($this->invokable, func_get_args());
		else
			throw new BadMethodCallException('Prototype is not invokable');
	}

	/**
	 * @see Serializable::serialize()
	 */
	public function serialize()
	{
		$properties = array();
		$properties['#'] = $this->invokable instanceof Closure ? static::serializeClosure($this->invokable) : null;

		foreach ( $this->properties as $name => $value ) {
			if ( $value instanceof Closure )
				$properties["closure_$name"] = static::serializeClosure($value);
			else
				$properties["mixed_$name"] = serialize($value);
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
			if ( $name == '#' )
				$this->invokable = empty($value) ? null : eval($value);
			elseif ( substr($name, 0, strlen('closure_')) == 'closure_' )
				$this->properties[substr($name, strlen('closure_'))] = eval($value);
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