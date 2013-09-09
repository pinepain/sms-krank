<?php

namespace SMSKrank\PhoneNumbers\Parsers;

use SMSKrank\PhoneNumbers\Parsers\Simple;

class PhoneWord extends Simple
{
    protected $table = array(
        'A' => 2, 'B' => 2, 'C' => 2,
        'D' => 3, 'E' => 3, 'F' => 3,
        'G' => 4, 'H' => 4, 'I' => 4,
        'J' => 5, 'K' => 5, 'L' => 5,
        'M' => 6, 'N' => 6, 'O' => 6,
        'P' => 7, 'Q' => 7, 'R' => 7, 'S' => 7,
        'T' => 8, 'U' => 8, 'V' => 8,
        'W' => 9, 'X' => 9, 'Y' => 9, 'Z' => 9,
    );

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
        if (preg_match('/[A-Za-z]/', $number)) {
            $number = $this->translatePhoneWorlds($number);
        }

        return parent::parse($number);
    }

    protected function translatePhoneWorlds($number)
    {
        return strtr(strtoupper($number), $this->table);
    }
}

