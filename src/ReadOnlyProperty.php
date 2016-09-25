<?php
namespace TassoEvan\Prototype;

use \UnexpectedValueException;

class ReadOnlyProperty extends Property
{
    protected $value;
    protected $strict;

    public function __construct($value = null, $strict = false)
    {
        $this->value = $value;
        $this->strict = $strict;
    }

    public function get()
    {
        return $this->value;
    }

    public function set($newValue)
    {
        if ($this->strict) {
            throw new UnexpectedValueException('property is read only');
        }

        return $this->value;
    }
}
