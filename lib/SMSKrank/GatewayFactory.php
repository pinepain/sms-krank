<?php

namespace SMSKrank;

use SMSKrank\Utils\AbstractLoader;

class GatewayFactory
{
    private $gateways_loader;
    private $pool = array();

    public function __construct(AbstractLoader $gateways_loader)
    {
        $this->gateways_loader = $gateways_loader;
    }

    // TODO: test one_shot = true/false
    /**
     * @param string $gate_name
     * @param bool   $one_shot
     *
     * @return GatewayInterface
     *
     * @throws Exceptions\GatewayFactoryException
     */
    public function getGateway($gate_name, $one_shot = false)
    {
        // TODO: implement dirty flag on Gateway class to force pool item reinitialization
        if ($one_shot || !isset($this->pool[$gate_name])) {

            $config = $this->gateways_loader->get($gate_name);
            // TODO: store initialized objects
            $r        = new \ReflectionClass($config['class']);
            $instance = $r->newInstanceArgs($config['args']);

            if ($one_shot) {
                return $instance;
            }

            $this->pool[$gate_name] = $instance;
        }

        return $this->pool[$gate_name];
    }

    // TODO: test
    public function clearPool($gate_name = null)
    {
        if ($gate_name) {
            unset($this->pool[$gate_name]);
        } else {
            $this->pool = array();
        }
//        return $this;
    }
}