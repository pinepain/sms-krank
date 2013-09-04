<?php

namespace SMSKrank\Helpers\Gateways\Valid;

use SMSKrank\Gateways\AbstractGateway;
use SMSKrank\Message;
use SMSKrank\PhoneNumber;

class ConstructorWithAllRequired extends AbstractGateway
{
    public function __construct($foo, $bar, $baz)
    {
    }

    public function send(PhoneNumber $number, Message $message, \DateTime $schedule = null)
    {
    }
}