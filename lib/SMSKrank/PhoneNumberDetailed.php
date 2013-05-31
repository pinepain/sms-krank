<?php

namespace SMSKrank;

use SMSKrank\Exceptions\PhoneNumberDetailedException;

class PhoneNumberDetailed extends PhoneNumber
{
    private $codes;
    private $geo;
    private $props;

    public function __construct($phone_number, array $calling_codes, array $geo, array $props)
    {
        parent::__construct($phone_number);

        $this->codes = $calling_codes;
        $this->geo   = $geo;
        $this->props = $props;
    }

    public function getCode($field = null)
    {
        if (null !== $field) {
            if (!isset($this->codes[$field])) {
                throw new PhoneNumberDetailedException("Phone number doesn't contain calling code for '{$field}' field");
            }

            return $this->codes[$field];
        }

        return $this->codes;
    }

    public function getGeo($field = null)
    {
        if (null !== $field) {
            if (!isset($this->geo[$field])) {
                throw new PhoneNumberDetailedException("Phone number doesn't contain geo information for '{$field}' field");
            }

            return $this->geo[$field];
        }

        return $this->geo;
    }

    public function hasProp($field) {
        return isset($this->props[$field]);
    }

    public function getProp($field = null) {
        if (null !== $field) {
            if (!isset($this->props[$field])) {
                throw new PhoneNumberDetailedException("Phone number property '{$field}' doesn't exists");
            }

            return $this->props[$field];
        }

        return $this->geo;
    }

    public function isMobile()
    {
        try {
            return $this->getProp('type') === 'mobile';
        } catch (\Exception $e) {
            return false;
        }
    }


//    public function getGeo ($field = null) {
//
//    }
}
