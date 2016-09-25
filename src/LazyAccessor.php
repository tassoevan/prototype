<?php
namespace TassoEvan\Prototype;

use \BadMethodCallException;
use \Closure;

class LazyAccessor implements Accessor
{
    public function &get(Prototype $obj, &$property)
    {
        if ($property instanceof Closure || (is_object($property) && method_exists($property, '__invoke'))) {
            $property = [call_user_func($property)];
        }

        return $property[0];
    }

    public function &set(Prototype $obj, &$property, &$value)
    {
        $property = [$value];

        return $property;
    }

    public function &invoke(Prototype $obj, &$property, &...$args)
    {
        if ($property instanceof Closure || (is_object($property) && method_exists($property, '__invoke'))) {
            $property = [call_user_func($property)];
        }

        if ($property[0] instanceof Closure || (is_object($property[0]) && method_exists($property[0], '__invoke'))) {
            $ret = call_user_func_array($property[0], $args);
            return $ret;
        }
        else {
            throw new BadMethodCallException();
        }
    }
}
