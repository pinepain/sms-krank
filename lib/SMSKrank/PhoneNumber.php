<?php

namespace SMSKrank;

use SMSKrank\Exceptions\PhoneNumberException;

class PhoneNumber {
    private $number;

    public function __construct($phone_number) {
        $phone_number = preg_replace('/[^0-9]/', '', $phone_number);

        if (!strlen($phone_number)) {
            throw new PhoneNumberException('Empty phone number');
        }

        $this->number = $phone_number;
    }

    public function getNumber() {
        return $this->number;
    }
}