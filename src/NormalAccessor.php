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
        if ($property instanceof Closure || (is_object($property) && method_exists($property, '__invoke'))) {
            $value = call_user_func_array($property, $args);
            return $value;
        }
        else {
            throw new BadMethodCallException();
        }
    }
}
