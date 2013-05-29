<?php

namespace SMSKrank\Utils;

use SMSKrank\Utils\Exceptions\ZonesLoaderException;
use Symfony\Component\Yaml\Yaml;

use SMSKrank\Utils\Exceptions\GatesLoaderException;

class GatewaysLoader
{
    private $gate_interface_class = "\\SMSKrank\\GatewayInterface";
    public $gates = array();
    private $source;

    public function __construct($source)
    {
        if (!file_exists($source)) {
            throw new GatesLoaderException('Source does not exists');
        }

        $this->source = $source;
    }

    public function load($group = null)
    {
        if (null === $group) {

            if (is_file($this->source)) {

                $group_file = $this->source;

                $_parts = pathinfo($group_file);
                $group = $_parts['filename']; // since PHP 5.2.0

            } else {

                $dir_content = array_filter(
                    scandir($this->source),
                    function ($val) {
                        return $val[0] != '.' && substr($val, -5) == '.yaml';
                    }
                );

                foreach ($dir_content as $group) {
                    $this->load(substr($group, 0, -5));
                }

                return $this->gates;

            }

        } else {
            if (is_file($this->source)) {
                throw new GatesLoaderException('Source directory is file');
            }

            $group_file = $this->source . DIRECTORY_SEPARATOR . $group . '.yaml';
        }

        if (!file_exists($group_file)) {
            throw new GatesLoaderException("Group file '{$group}' does not exists");
        }

        if (!is_readable($group_file)) {
            throw new GatesLoaderException("Group file '{$group}' is not readable");
        }

        $group_data = Yaml::parse(file_get_contents($group_file));

        if (!is_array($group_data)) {
            throw new GatesLoaderException("Garbage in group file '{$group}'");
        }

        $validated = $this->validateGroupData($group_data);

        $this->gates =array_merge($this->gates, $validated);

        ksort($this->gates);

        return $this->gates;
    }

    private function validateGroupData(array $data)
    {
        $out = array();

        foreach ($data as $gate_name => $gate_params) {
            if (!isset($gate_params['class'])) {
                throw new GatesLoaderException("Missed gateway class in '{$gate_name}' gate description");
            }

            if (!class_exists($gate_params['class'])) {
                throw new GatesLoaderException("Gateway class doesn't exists in '{$gate_name}' gate description");
            }

            if (!isset($gate_params['args'])) {
                throw new GatesLoaderException("Missed gateway arguments in '{$gate_name}' gate description");
            }

            if (!is_array($gate_params['args'])) {
                throw new GatesLoaderException("Gateway class arguments has wrong type (should be array) in '{$gate_name}' gate description");
            }

            $gate = new \ReflectionClass($gate_params['class']);

            if (!$gate->implementsInterface($this->gate_interface_class)) {
                throw new GatesLoaderException("Gateway class '{$gate_params['class']}' doesn't implement standard gate interface '{$this->gate_interface_class}' in '{$gate_name}' gate description");
            }

            $config_args = $gate_params['args'];
            $args = array();

            foreach ($gate->getConstructor()->getParameters() as $param) {

                if (!isset($config_args[$param->getName()])) {

                    if (!$param->isDefaultValueAvailable()) {
                        throw new GatesLoaderException("Missed argument '{$param->getName()}' for  '{$this->gate_interface_class}' in '{$gate_name}' gate description");
                    }

                    $args[] = $param->getDefaultValue();

                } else {
                    $args[] = $config_args[$param->getName()];
                }

                $out[$gate_name] = array('class' => $gate_params['class'], 'args' => $args);
            }
        }

        return $out;
    }
}