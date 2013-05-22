<?php

namespace SMSKrank;


class PhoneNumberInfo {
    private $country_calling_code;
    private $country_alpha2_code;

    public function __construct($country_calling_code, $country_alpha2_code) {
        $this->country_calling_code = $country_calling_code;
        $this->country_alpha2_code = $country_alpha2_code;
    }

    public function getCountryCallingCode() {
        return $this->country_calling_code;
    }

    public function getCountryAlpha2Code() {
        return $this->country_alpha2_code;
    }
}
