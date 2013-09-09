<?php

namespace SMSKrank\PhoneNumbers\Parsers;

class Simple implements PhoneNumberParserInterface
{
    /**
     * Remove unwanted characters from phone number and possibly make additional transformation to normalize phone number
     *
     * @param string $number Phone number to process
     *
     * @return string Phone number as numerical string
     * @throws ParserException When phone number cannot be parsed, e.g. contains invalid characters or empty
     */
    public function parse($number)
    {
        $number = preg_replace('/[^0-9]/', '', $number);

        if (!strlen($number)) {
            throw new ParserException('Empty phone number');
        }

        return $number;
    }
}
