<?php

namespace SMSKrank\Helpers\Gateways\Valid;

use SMSKrank\Gateways\AbstractGateway;
use SMSKrank\Message;
use SMSKrank\PhoneNumber;

class ConstructorWithAllDefault extends AbstractGateway
{
    public function __construct($foo = 1, $bar = 2, $baz = 3)
    {
    }

    public function send(PhoneNumber $number, Message $message, \DateTime $schedule = null)
    {
    }
}