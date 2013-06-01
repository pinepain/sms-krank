<?php

namespace SMSKrank\Utils;

use SMSKrank\Utils\Exceptions\LoaderException;

class GatewaysMapLoader extends AbstractLoader
{
    private $gates_loader;

    public function __construct($source, GatewaysLoader $gateways_loader = null)
    {
        parent::__construct($source); // TODO: Change the autogenerated stub
        $this->gates_loader = $gateways_loader;
    }

    protected function postLoad(array $loaded, $what)
    {
        foreach ($loaded as $gate_name => $params) {
            if (!is_array($params)) {
                if ('any' === $params) {
                    $loaded[$gate_name] = null;
                } else {
                    throw new LoaderException("Invalid params for '{$gate_name}' in '{$what}' container");
                }
            }

            if ($this->gates_loader) {
                if (!$this->gates_loader->has($gate_name)) {
                    throw new LoaderException("Gateway '{$gate_name}' from '{$what}' container doesn't exists");
                }
            }
        }

        return array($what => $loaded);
    }
}