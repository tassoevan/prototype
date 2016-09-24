<?php
namespace TassoEvan\Prototype;

use \BadMethodCallException;
use \Closure;

class DynamicAccessor implements Accessor
{
    public function &get(Prototype $obj, &$property)
    {
        $callable = &$property[0];

        if ($callable instanceof Closure) {
            $value = $callable->call($obj);
            return $value;
        }
        elseif (is_object($callable) && method_exists($callable, '__invoke')) {
            return call_user_func($callable);
        }
        else {
            $dummy = null;
            return $dummy;
        }
    }

    public function &set(Prototype $obj, &$property, &$value)
    {
        $callable = &$property[1];

        if ($callable instanceof Closure) {
            $value = $callable->call($obj, $value);
            return $value;
        }
        elseif (is_object($callable) && method_exists($callable, '__invoke')) {
            return call_user_func($callable, $value);
        }
        else {
            $dummy = null;
            return $dummy;
        }
    }

    public function &invoke(Prototype $obj, &$property, &...$args)
    {
        $callable = &$property[2];

        if ($callable instanceof Closure) {
            $value = $callable->call($obj, ...$args);
            return $value;
        }
        elseif (is_object($callable) && method_exists($callable, '__invoke')) {
            return call_user_func_array($callable, $args);
        }
        else {
            throw new BadMethodCallException();
        }
    }
}
