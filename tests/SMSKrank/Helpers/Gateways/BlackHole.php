<?php

namespace SMSKrank\Helpers\Gateways;

use SMSKrank\PhoneNumber;
use SMSKrank\Message;
use SMSKrank\GatewayInterface;

class BlackHole implements GatewayInterface
{

    public function __construct($login, $password='default')
    {
    }

    public function send(PhoneNumber $number, Message $message, \DateTime $schedule = null)
    {
        return null; // not available
    }

    public function getBalance()
    {
        return null; // not available
    }
}