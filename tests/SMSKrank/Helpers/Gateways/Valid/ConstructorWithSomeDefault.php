<?php

namespace SMSKrank\Helpers\Gateways\Valid;

use SMSKrank\Gateways\AbstractGateway;
use SMSKrank\Message;
use SMSKrank\PhoneNumber;

class ConstructorWithSomeDefault extends AbstractGateway
{
    public function __construct($foo, $bar, $baz = 3, $tar = 4)
    {
    }

    public function send(PhoneNumber $number, Message $message, \DateTime $schedule = null)
    {
    }
}