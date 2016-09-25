<?php
namespace TassoEvan\Prototype;

use \BadMethodCallException;
use \Closure;

class DynamicAccessor implements Accessor
{
    public function &get(Prototype $obj, &$property)
    {
        $callable = &$property[0];

        if ($callable instanceof Closure || (is_object($callable) && method_exists($callable, '__invoke'))) {
            $ret = call_user_func($callable);
        }
        else {
            $ret = null;
        }

        return $ret;
    }

    public function &set(Prototype $obj, &$property, &$value)
    {
        $callable = &$property[1];

        if ($callable instanceof Closure || (is_object($callable) && method_exists($callable, '__invoke'))) {
            $ret = call_user_func($callable, $value);
        }
        else {
            $ret = null;
        }

        return $ret;
    }

    public function &invoke(Prototype $obj, &$property, &...$args)
    {
        $callable = &$property[2];

        if ($callable instanceof Closure || (is_object($callable) && method_exists($callable, '__invoke'))) {
            $ret = call_user_func_array($callable, $args);

            return $ret;
        }
        else {
            throw new BadMethodCallException();
        }
    }
}
