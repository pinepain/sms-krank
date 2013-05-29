<?php

namespace SMSKrank;

use SMSKrank\Exceptions\GatewayFactoryException;

class GatewayFactory
{
    private $gateways;

    public function __construct(array $gateways)
    {
        $this->gateways = $gateways;
    }

    /**
     * @param $gate_name
     *
     * @return GatewayInterface
     *
     * @throws Exceptions\GatewayFactoryException
     */
    public function getGateway($gate_name)
    {
        if (!isset($this->gateways[$gate_name])) {
            throw new GatewayFactoryException("Gateway doesn't exists");
        }

        $gate_config = $this->gateways[$gate_name];

        $r = new \ReflectionClass($gate_config['class']);
        return $r->newInstanceArgs($gate_config['args']);
    }
}