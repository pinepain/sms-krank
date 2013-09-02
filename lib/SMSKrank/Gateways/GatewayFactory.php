<?php

namespace SMSKrank;

use SMSKrank\Loaders\AbstractFileLoader;
use SMSKrank\Utils\Options;
use SMSKrank\Utils\Packer;

class GatewayFactory
{
    private $gateways_loader;
    private $packer;
    private $options;

    private $pool = array();

    public function __construct(AbstractFileLoader $gateways_loader, Packer $packer = null, array $options = array())
    {
        $this->gateways_loader = $gateways_loader;
        $this->packer          = $packer;
        $this->options         = new Options($options);
    }

    /**
     * Get current packer
     *
     * @return Packer | null Current packer
     */
    public function getPacker()
    {
        return $this->packer;
    }

    /**
     * Set packer
     *
     * @param Packer | null $packer
     *
     * @return Packer | null Old packer
     */
    public function setPacker(Packer $packer = null)
    {
        $old = $this->packer;

        // set packer for new gates
        $this->packer = $packer;

        // set packer on all initialized gates
        /** @var GatewayInterface $instance */
        foreach ($this->pool as $instance) {
            $instance->setPacker($this->packer);
        }

        return $old;
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
            $r = new \ReflectionClass($config['class']);
            /** @var GatewayInterface $instance */
            $instance = $r->newInstanceArgs($config['args']);

            $instance->setPacker($this->packer);
            $instance->options()->set($config['options']);

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