<?php

namespace SMSKrank;

interface GatewayInterface {

    public function send(Number $number, Message $message, \DateTime $schedule = null);

    public function getBalance();
}