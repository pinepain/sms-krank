<?php

namespace SMSKrank\Loaders\Parsers;

use SMSKrank\Loaders\LoaderInterface;
use SMSKrank\Loaders\Parsers\Exceptions\GatewaysParserException;

class GatewaysParser implements ParserInterface
{
    private $sender_interface = '\SMSKrank\Interfaces\GatewayInterface';

    public function parse(array $data, $section, LoaderInterface $loader)
    {
        if (!isset($data['class'])) {
            throw new GatewaysParserException("Gateway '{$section}' class missed");
        } elseif (!class_exists($data['class'])) {
            throw new GatewaysParserException("Gateway '{$section}' class '{$data['class']}' doesn't exists");
        }

        if (!isset($data['args'])) {
            $data['args'] = array();
        } elseif (!is_array($data['args'])) {
            throw new GatewaysParserException("Gateway '{$section}' arguments should be array");
        }

        if (!isset($data['options'])) {
            $data['options'] = array();
        } elseif (!is_array($data['options'])) {
            throw new GatewaysParserException("Gateway '{$section}' options should be array");
        }

        $data['args'] = $this->getGateArguments($data['class'], $data['args']);

        return $data;
    }

    protected function getGateArguments($class, $arguments)
    {
        $reflector = new \ReflectionClass($class);

        if (!$reflector->implementsInterface($this->sender_interface)) {
            throw new GatewaysParserException("Gateway class '{$class}' doesn't implement interface '{$this->sender_interface}'");
        }

        $args = array();

        if ($reflector->getConstructor()) {
            $parameters = $reflector->getConstructor()->getParameters();
        } else {
            $parameters = array();
        }

        foreach ($parameters as $param) {
            $param_name = $param->getName();

            if (!isset($arguments[$param_name])) {

                if (!$param->isDefaultValueAvailable()) {
                    throw new GatewaysParserException("Gateway class '{$class}' constructor argument '{$param_name}' missed");
                }

                $args[] = $param->getDefaultValue();

            } else {
                $args[] = $arguments[$param_name];
            }
        }

        return $args;
    }
}