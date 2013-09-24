<?php

namespace SMSKrank\Tests\PhoneNumbers;

use SMSKrank\PhoneNumbers\Plain;

class PhoneNumberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @cover \SMSKrank\PhoneNumbers\Plain::__construct
     * @cover \SMSKrank\PhoneNumbers\Plain::number
     */
    public function testAll()
    {
        $number = '12345';
        $object = new Plain($number);
        $this->assertEquals($number, $object->number());

        $number = 'complete bullshit';
        $object = new Plain($number);
        $this->assertEquals($number, $object->number());
    }
}
