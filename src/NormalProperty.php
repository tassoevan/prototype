<?php
namespace TassoEvan\Prototype;

class NormalProperty extends Property
{
    protected $value;

    public function __construct($value = null)
    {
        $this->value = $value;
    }

    public function get()
    {
        return $this->value;
    }

    public function set($newValue)
    {
        return $this->value = $newValue;
    }
}
