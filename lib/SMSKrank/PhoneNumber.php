<?php

namespace SMSKrank;


class PhoneNumber {
    private $number;

    public function __construct($phone_number) {
        $this->number = $phone_number;
    }

    public function getNumber() {
        return $this->number;
    }

    public function getZone() {
        return $this->number[0];
    }

//    public function getCountry() {
//        throw new \Exception('Not Implemented Yet');
//    }
}