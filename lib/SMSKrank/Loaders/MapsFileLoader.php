<?php

namespace SMSKrank\Loaders;

use SMSKrank\Loaders\Exceptions\MapsLoaderException;

class MapsFileLoader extends AbstractFileLoader
{
    private $gates_loader;

    public function __construct($source, GatewaysFileLoader $gateways_loader = null)
    {
        parent::__construct($source);
        $this->gates_loader = $gateways_loader;
    }

    protected function postLoad(array $loaded, $what)
    {
        foreach ($loaded as $gate_name => $params) {
            if (!is_array($params)) {
                if ('any' === $params) {
                    $loaded[$gate_name] = null;
                } else {
                    throw new MapsLoaderException("Invalid params for map '{$gate_name}' in '{$what}' container");
                }
            }

            if ($this->gates_loader) {
                if (!$this->gates_loader->has($gate_name)) {
                    throw new MapsLoaderException("Gateway '{$gate_name}' from '{$what}' container doesn't exists");
                }
            }
        }

        return array($what => $loaded);
    }
}