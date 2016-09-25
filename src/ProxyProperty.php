<?php
namespace TassoEvan\Prototype;

use \Closure;

class ProxyProperty extends Property
{
    protected $getMiddleware;
    protected $setMiddleware;
    protected $property;

    public function __construct(callable $getMiddleware = null, callable $setMiddleware = null, $propertyOrValue = null)
    {
        $this->getMiddleware = $getMiddleware;
        $this->setMiddleware = $setMiddleware;

        if ($propertyOrValue instanceof Property) {
            $this->property = $propertyOrValue;
        }
        else {
            $this->property = new NormalProperty($propertyOrValue);
        }
    }

    public function attachTo(Prototype $prototype = null)
    {
        parent::attachTo($prototype);
        $this->property->attachTo($prototype);
    }

    public function get()
    {
        $callable = $this->getMiddleware instanceof Closure ?
            $this->getMiddleware->bindTo($this->prototype) :
            $this->getMiddleware;

        if (is_callable($callable)) {
            return call_user_func($callable, $this->property);
        }
        else {
            return $this->property->get();
        }
    }

    public function set($newValue)
    {
        $callable = $this->setMiddleware instanceof Closure ?
            $this->setMiddleware->bindTo($this->prototype) :
            $this->setMiddleware;

        if (is_callable($callable)) {
            return call_user_func($callable, $this->property, $newValue);
        }
        else {
            return $this->property->set($newValue);
        }
    }
}
