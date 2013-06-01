<?php

namespace SMSKrank;

interface GatewayDetailedInterface extends GatewayInterface {
    /**
     * @return float | null Account balance, if available. Null otherwise
     */
    public function getBalance();

    // TODO: use case is to check credentials and service availability
//    /**
//     * Check gate status
//     *
//     * @return bool | null Gate status, if available. Null otherwise
//     */
//    public function test();
}