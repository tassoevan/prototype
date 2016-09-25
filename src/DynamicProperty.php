<?php
namespace TassoEvan\Prototype;

use \Closure;

class DynamicProperty extends Property
{
    protected $getter;
    protected $setter;

    public function __construct(callable $getter = null, callable $setter = null)
    {
        $this->getter = $getter;
        $this->setter = $setter;
    }

    public function get()
    {
        $callable = $this->getter instanceof Closure ?
            $this->getter->bindTo($this->prototype) :
            $this->getter;

        if (is_callable($callable)) {
            return call_user_func($callable);
        }
    }

    public function set($newValue)
    {
        $callable = $this->setter instanceof Closure ?
            $this->setter->bindTo($this->prototype) :
            $this->setter;

        if (is_callable($callable)) {
            return call_user_func($callable, $newValue);
        }
    }
}
