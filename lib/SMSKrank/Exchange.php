<?php

namespace SMSKrank;


$gates_map = array(
    'RU' => array()
);

// TODO: implement gateway interface
class Exchange {
    private $pool;

    public function __construct(GatewayFactory $factory, array $gates_settings, Directory $directory) {
        $this->pool = $gates_map;
    }

    public function send(PhoneNumber $number, Message $message, \DateTime $schedule = null) {
    }

    /**
     * @param PhoneNumber $number
     *
     * @return GatewayInterface
     */
    private function getGateForPhoneNumber(PhoneNumber $number) {

    }

}