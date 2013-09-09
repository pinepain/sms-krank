<?php

namespace SMSKrank\PhoneNumbers\Parsers;

use SMSKrank\PhoneNumbers\Parsers\ParserException;

interface PhoneNumberParserInterface
{
    /**
     * Remove unwanted characters from phone number and possibly make additional transformation to normalize phone number
     *
     * @param string $number Phone number to process
     *
     * @return string Phone number as numerical string
     * @throws ParserException When phone number cannot be parsed, e.g. contains invalid characters or empty
     */
    public function parse($number);
}