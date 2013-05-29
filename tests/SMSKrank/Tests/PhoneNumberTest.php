<?php

namespace SMSKrank\Tests;

use SMSKrank\PhoneNumber;

class PhoneNumberTest extends \PHPUnit_Framework_TestCase {

    /**
     * @cover \SMSKrank\PhoneNumber::__construct
     * @cover \SMSKrank\PhoneNumber::getNumber
     */
    public function testGetNumber()
    {
        $number = '12345';
        $object = new PhoneNumber($number);
        $this->assertEquals($number, $object->getNumber());
    }
}
