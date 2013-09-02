<?php

namespace SMSKrank;

use SMSKrank\Exceptions\PhoneNumberDetailedException;
use SMSKrank\Utils\Options;

class PhoneNumberDetailed extends PhoneNumber
{
    private $codes;
    private $geo;
    private $props;

    public function __construct($phone_number, array $calling_codes, array $geo, array $props)
    {
        parent::__construct($phone_number);

        $this->codes = new Options($calling_codes);
        $this->geo   = new Options($geo);
        $this->props = new Options($props);
    }

    public function codes()
    {
        return $this->codes;
    }

    public function geo()
    {
        return $this->geo;
    }

    public function props()
    {
        return $this->props;
    }

//    public function is($type)
//    {
//        return ($this->props()->get('type') == $type);
//    }

//    public function isMobile()
//    {
//        return ($this->props()->get('type') == 'mobile');
//    }

}
