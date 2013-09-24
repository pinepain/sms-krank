<?php

namespace SMSKrank\Tests\PhoneNumbers;

use SMSKrank\PhoneNumbers\Detailed;

class DetailedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @cover \SMSKrank\PhoneNumbers\Detailed::__construct
     * @cover \SMSKrank\PhoneNumbers\Detailed::number
     * @cover \SMSKrank\PhoneNumbers\Detailed::geo
     * @cover \SMSKrank\PhoneNumbers\Detailed::codes
     * @cover \SMSKrank\PhoneNumbers\Detailed::props
     */
    public function testAll()
    {
        $number = '12345';
        $geo    = array('geo array');
        $codes  = array('codes array');
        $props  = array('props array');

        $object = new Detailed($number, $codes, $geo, $props);

        $this->assertEquals($number, $object->number());

        $this->assertInstanceOf('\SMSKrank\Utils\Options', $object->geo());
        $this->assertInstanceOf('\SMSKrank\Utils\Options', $object->codes());
        $this->assertInstanceOf('\SMSKrank\Utils\Options', $object->props());

        $this->assertEquals($geo, $object->geo()->all());
        $this->assertEquals($codes, $object->codes()->all());
        $this->assertEquals($props, $object->props()->all());

        $number = 'complete bullshit';
        $object = new Detailed($number, $codes, $geo, $props);
        $this->assertEquals($number, $object->number());
    }
}
