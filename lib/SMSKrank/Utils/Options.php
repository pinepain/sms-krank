<?php

namespace SMSKrank\Utils;

use SMSKrank\Utils\Exceptions\OptionsException;

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

    public function getOrFail($name)
    {
        if ($this->has($name)) {
            return $this->container[$name];
        }

        throw new OptionsException("Option '{$name}' doesn't exists");
    }

    public function all()
    {
        return $this->container;
    }

    /**
     * Set option values
     *
     * @param string|array|Options $name     Option name or array of options or Options object
     * @param mixed                $value    Options value. Interpreted as $override parameter when multiple options set at once
     * @param bool                 $override Should new options override existent ones.
     */
    public function set($name, $value = null, $override = true)
    {
        if ($name instanceof Options) {
            $name = $name->all();
        }

        if (is_array($name)) {
            $override = $value === null ? $override : $value;
            foreach ($name as $n => $v) {
                $this->set($n, $v, $override);
            }

            return;
        }

        if ($override || !$this->has($name)) {
            $this->container[$name] = $value;
        }
    }

    public function has($name)
    {
        if ($name instanceof Options) {
            $name = $name->all();
        }

        if (!is_array($name)) {
            $name = func_get_args();
        }

        foreach ($name as $n) {
            if (!isset($this->container[$n])) {
                return false;
            }
        }

        return true;
    }

    public function requires($name)
    {
        if ($name instanceof Options) {
            $name = $name->all();
        }

        if (!is_array($name)) {
            $name = func_get_args();
        }

        foreach ($name as $n) {
            if (!$this->has($n)) {
                throw new OptionsException("Missed required option '{$n}'");
            }
        }
    }

    public function del($name)
    {
        if ($name instanceof Options) {
            $name = $name->all();
        }

        if (!is_array($name)) {
            $name = func_get_args();
        }

        foreach ($name as $n) {
            unset($this->container[$n]);
        }
    }

    public function replace($options)
    {
        if ($options instanceof Options) {
            $options = $options->all();
        }

        if (!is_array($options)) {
            $options = array_fill_keys(func_get_args(), null);
        }

        $old = $this->container;

        $this->container = $options;

        return $old;
    }
}