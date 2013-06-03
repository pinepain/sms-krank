<?php

namespace SMSKrank\Tests;

use SMSKrank\Message;

class MessageTest extends \PHPUnit_Framework_TestCase
{

    public function providerMessage()
    {
        return array(
            // 7-bit encoding
            array('short message', 13),
            // we do nt handle spaces removal due to ambiguity with rtl languages, so no trim
            array('   short message', 16),
//            array("\t\t\tshort message", 16),
//            array("\n\n\nshort message", 16),
//            array("\r\r\rshort message", 16),
            array('short message   ', 16),
            array("short message\t", 14),
            array("short message\n", 14),
            array("short message\r", 14),
            array("\tshort message", 14),
            array("\nshort message", 14),
            array("\rshort message", 14),
            array("short\nmessage", 13),
            array("short\n\nmessage", 14),
            array("short\tmessage", 13),
            array("short\rmessage", 13),
            array(str_repeat('test', 40), 160),
            array(str_repeat('test', 40) . '+', 161),
//            // 16-bit encodings
            array('тест', 8),
            array(str_repeat('тест', 20), 160),
            array('اختباررر', 16),
            array(str_repeat('اختباررر', 10), 160),
            array('מבחן', 8),
            array(str_repeat('מבחן', 20), 160),
            array('测试', 6),
            array(str_repeat('测试', 27), 162),
            array('데모', 6),
            array(str_repeat('데모', 27), 162),
        );
    }

    /**
     * @covers       \SMSKrank\Message::__construct
     * @covers       \SMSKrank\Message::getText
     * @covers       \SMSKrank\Message::getLength
     *
     * @dataProvider providerMessage
     */
    public function test($message, $len)
    {
        $obj = new Message($message);
        $this->assertEquals($message, $obj->getText());
        $this->assertEquals($len, $obj->getLength());
    }
}
