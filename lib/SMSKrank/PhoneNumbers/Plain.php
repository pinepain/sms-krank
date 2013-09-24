<?php

namespace SMSKrank\PhoneNumbers;

class Plain
{
    private $number;

    public function __construct($number)
    {
        $this->number = $number;
    }

    public function number()
    {
        return $this->number;
    }
}