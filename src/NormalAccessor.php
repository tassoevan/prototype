<?php
namespace TassoEvan\Prototype;

use \BadMethodCallException;
use \Closure;

class NormalAccessor implements Accessor
{
    public function &get(Prototype $obj, &$property)
    {
        return $property;
    }

    public function &set(Prototype $obj, &$property, &$value)
    {
        $property = $value;

        return $property;
    }

    public function &invoke(Prototype $obj, &$property, &...$args)
    {
        if ($property instanceof Closure) {
            $value = $property->call($obj, ...$args);
            return $value;
        }
        elseif (is_object($property) && method_exists($property, '__invoke')) {
            return call_user_func_array($property, $args);
        }
        else {
            throw new BadMethodCallException();
        }
    }
}
