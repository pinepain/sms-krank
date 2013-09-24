<?php

namespace SMSKrank\PhoneNumbers;

use SMSKrank\Utils\Options;

class Detailed extends Plain
{
    private $codes;
    private $geo;
    private $props;

    public function __construct($number, array $codes, array $geo, array $props)
    {
        parent::__construct($number);

        $this->codes = new Options($codes);
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
}
