<?php

namespace SMSKrank;

use SMSKrank\Utils\AbstractLoader;

class GatewayFactory
{
    private $gateways_loader;

    public function __construct(AbstractLoader $gateways_loader)
    {
        $this->gateways_loader = $gateways_loader;
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
        $config = $this->gateways_loader->get($gate_name);
        // TODO: store initialized objects
        $r = new \ReflectionClass($config['class']);
        return $r->newInstanceArgs($config['args']);
    }
}