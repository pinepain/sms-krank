<?php

namespace SMSKrank\Utils;

use Symfony\Component\Yaml\Yaml;

use SMSKrank\Utils\Exceptions\LoaderException;


//TODO: extends AbstractLoader, unit tests
class GatewaysLoader extends AbstractLoader
{
    private $gate_interface_class = "\\SMSKrank\\GatewayInterface";

    protected function postLoad(array $loaded, $what)
    {
        $out = array();

        foreach ($loaded as $gate_name => $gate_params) {
            if (!isset($gate_params['class'])) {
                throw new LoaderException("Missed gateway class in '{$gate_name}' gate description for");
            }

            if (!class_exists($gate_params['class'])) {
                throw new LoaderException("Gateway class doesn't exists in '{$gate_name}' gate description");
            }

            if (!isset($gate_params['args'])) {
                throw new LoaderException("Missed gateway arguments in '{$gate_name}' gate description");
            }

            if (!is_array($gate_params['args'])) {
                throw new LoaderException("Gateway class arguments has wrong type (should be array) in '{$gate_name}' gate description");
            }

            $gate = new \ReflectionClass($gate_params['class']);

            if (!$gate->implementsInterface($this->gate_interface_class)) {
                throw new LoaderException("Gateway class '{$gate_params['class']}' doesn't implement standard gate interface '{$this->gate_interface_class}' in '{$gate_name}' gate description");
            }

            $config_args = $gate_params['args'];
            $args = array();

            foreach ($gate->getConstructor()->getParameters() as $param) {

                if (!isset($config_args[$param->getName()])) {

                    if (!$param->isDefaultValueAvailable()) {
                        throw new LoaderException("Missed argument '{$param->getName()}' for  '{$this->gate_interface_class}' in '{$gate_name}' gate description");
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