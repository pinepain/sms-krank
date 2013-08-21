<?php

namespace SMSKrank\Utils;

class Options
{
    protected $container = array();

    public function __construct(array $options = null)
    {
        if ($options) {
            $this->container = $options;
        }
    }

    public function get($name, $default = null)
    {
        if ($this->has($name)) {
            return $this->container[$name];
        }

        return $default;
    }

    public function all()
    {
        return $this->container;
    }

    public function set($name, $value)
    {
        $this->container[$name] = $value;
    }

    public function has($name)
    {
        return isset($this->container[$name]);
    }

    public function del($name)
    {
        unset($this->container[$name]);
    }

    public function replace(array $options)
    {
        $old = $this->container;

        $this->container = $options;

        return $old;
    }
}