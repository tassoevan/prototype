<?php
namespace TassoEvan\Prototype;

interface Accessor
{
    public function &get(Prototype $obj, &$property);
    public function &set(Prototype $obj, &$property, &$value);
    public function &invoke(Prototype $obj, &$property, &...$args);
}
