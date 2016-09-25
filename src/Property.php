<?php
namespace TassoEvan\Prototype;

abstract class Property
{
    protected $prototype;

    public function attachTo(Prototype $prototype = null)
    {
        $this->prototype = $prototype;
    }

    public abstract function get();
    public abstract function set($newValue);
}
