<?php

namespace SMSKrank\Loaders;

use SMSKrank\Loaders\Exceptions\GatewaysParserException;

class GatewaysParser
{
    private $gate_interface_class = '\SMSKrank\Gateways\GatewayInterface';

    public function parse(array $data, $what)
    {
        $out = array();

        if (sizeof($data) != 1) {
            throw new GatewaysParserException("One file - one gateway");
        }

        if (!isset($data[$what])) {
            throw new GatewaysParserException("Wrong gateway name in file");
        }

        foreach ($data as $gate_name => $gate_params) {
            if (!isset($gate_params['class'])) {
                throw new GatewaysParserException("Missed gateway class in '{$gate_name}' gate description for");
            }

            if (!class_exists($gate_params['class'])) {
                throw new GatewaysParserException("Gateway class doesn't exists in '{$gate_name}' gate description");
            }

            if (!isset($gate_params['args'])) {
                throw new GatewaysParserException("Missed gateway arguments in '{$gate_name}' gate description");
            }

            if (!is_array($gate_params['args'])) {
                throw new GatewaysParserException("Gateway class arguments has wrong type (should be array) in '{$gate_name}' gate description");
            }

            if (!isset($gate_params['options'])) {
                $gate_params['options'] = array();
            } elseif (!is_array($gate_params['options'])) {
                throw new GatewaysParserException("Gateway class options has wrong type (should be array) in '{$gate_name}' gate description");
            }

            $gate = new \ReflectionClass($gate_params['class']);

            if (!$gate->implementsInterface($this->gate_interface_class)) {
                throw new GatewaysParserException("Gateway class '{$gate_params['class']}' doesn't implement standard gate interface '{$this->gate_interface_class}' in '{$gate_name}' gate description");
            }

            $config_args = $gate_params['args'];
            $args        = array();

            foreach ($gate->getConstructor()->getParameters() as $param) {

                if (!isset($config_args[$param->getName()])) {

                    if (!$param->isDefaultValueAvailable()) {
                        $param = $param->getName();
                        throw new GatewaysParserException("Missed argument '{$param}' for '{$this->gate_interface_class}'  in '{$gate_name}' gate description");
                    }

                    $args[] = $param->getDefaultValue();

                } else {
                    $args[] = $config_args[$param->getName()];
                }

                $out[$gate_name] = array(
                    'class'   => $gate_params['class'],
                    'args'    => $args,
                    'options' => $gate_params['options']
                );
            }
        }

        return $out;
    }
}