<?php

namespace SMSKrank;

interface GatewayInterface {
    /**
     * @param PhoneNumber $number
     * @param Message     $message
     * @param \DateTime   $schedule
     *
     * @return float | null Message fee, if available. Null otherwise
     */
    public function send(PhoneNumber $number, Message $message, \DateTime $schedule = null);
}