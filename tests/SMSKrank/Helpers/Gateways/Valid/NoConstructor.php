<?php

namespace SMSKrank\Helpers\Gateways\Valid;

use SMSKrank\Gateways\AbstractGateway;
use SMSKrank\Message;
use SMSKrank\PhoneNumber;

class NoConstructor extends AbstractGateway
{
    public function send(PhoneNumber $number, Message $message, \DateTime $schedule = null)
    {
    }
}