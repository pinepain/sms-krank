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

//    public function getCountry() {
//        throw new \Exception('Not Implemented Yet');
//    }
}