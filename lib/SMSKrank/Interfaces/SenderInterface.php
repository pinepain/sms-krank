<?php

namespace SMSKrank\Interfaces;

use SMSKrank\Message;
use SMSKrank\PhoneNumber;

interface SenderInterface
{
    /**
     * @param PhoneNumber $number
     * @param Message     $message
     * @param \DateTime   $schedule
     *
     * TODO: return result object with status, typical (required for all gates) methods and options, etc or throw exception on failure
     *
     * @return float | null Message fee, if available. Null otherwise
     */
    public function send(PhoneNumber $number, Message $message, \DateTime $schedule = null);
}