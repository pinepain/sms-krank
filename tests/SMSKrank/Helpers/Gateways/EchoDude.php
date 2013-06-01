<?php

namespace SMSKrank\Helpers\Gateways;

use SMSKrank\Exceptions\GatewayException;
use SMSKrank\PhoneNumber;
use SMSKrank\Message;
use SMSKrank\GatewayInterface;

class EchoDude implements GatewayInterface
{
    private $name;
    private $format;
    private $balance;
    private $price;

    public function __construct($name, $balance, $price, $format = '{phone} - {message} - {schedule}')
    {
        $this->name    = $name;
        $this->format  = $format;
        $this->balance = $balance;
        $this->price   = $price;

    }

    public function send(PhoneNumber $number, Message $message, \DateTime $schedule = null)
    {
        if ($this->balance - $this->price < 0) {
            throw new GatewayException("You don't have enough money on your balance");
        }

        $template = $this->format;

        $template = str_replace('{phone}', $number->getNumber(), $template);
        $template = str_replace(
            '{schedule}',
            $schedule ? $schedule->format(\DateTime::ISO8601) : 'immediately',
            $template
        );
        $template = str_replace('{message}', $message->getText(), $template);

        echo $template;
        $this->balance -= $this->price;

        return $this->price;
    }

    public function getBalance()
    {
        return $this->balance;
    }
}