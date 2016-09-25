<?php
namespace TassoEvan\Prototype;

use \Closure;

class LazyProperty extends Property
{
    protected $value;
    protected $loader;
    protected $loaded;

    public function __construct(callable $loader)
    {
        $this->loader = $loader;
        $this->loaded = false;
    }

    public function get()
    {
        if (!$this->loaded) {
            if ($this->loader instanceof Closure) {
                $this->value = call_user_func($this->loader->bindTo($this->prototype));
            }
            else {
                $this->value = call_user_func($this->loader);
            }
            $this->loaded = true;
        }

        return $this->value;
    }

    public function set($newValue)
    {
        $this->value = $newValue;
        $this->loaded = true;
        return $this->value;
    }
}
