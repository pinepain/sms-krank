<?php

namespace SMSKrank;

interface GatewayInterface {

    public function send(PhoneNumber $number, Message $message, \DateTime $schedule = null);

    public function getBalance();
}