<?php
/**
 * @author pba <bogdan.padalko@gmail.com>
 * @created 5/17/13 5:54 PM
 */

namespace SMSKrank\Tests;

use SMSKrank\Number;

class NumberTest extends \PHPUnit_Framework_TestCase {


    public function testGetNumber()
    {
        $number = '12345';
        $object = new Number($number);
        $this->assertEquals($object->getNumber(), $number);
    }
}
