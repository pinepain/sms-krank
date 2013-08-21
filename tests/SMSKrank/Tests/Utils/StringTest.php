<?php

namespace SMSKrank\Tests\Utils;

use SMSKrank\Utils\String;

class StringTest extends \PHPUnit_Framework_TestCase
{
    public function providerStringsNoPad()
    {
        return array(
            // ASCII
            array(12, 'hello world', '', 'hello world'),
            array(11, 'hello world', '', 'hello world'),
            array(10, 'hello world', '', 'hello worl'),
            array(6, 'hello world', '', 'hello'),
            array(5, 'hello world', '', 'hello'),
            array(4, 'hello world', '', 'hell'),
            // Unicode
            array(20, 'привет мир', '', 'привет мир'),
            array(19, 'привет мир', '', 'привет мир'),
            array(18, 'привет мир', '', 'привет ми'), // cut off half character
            array(17, 'привет мир', '', 'привет ми'), // cut off full character

            array(13, 'привет мир', '', 'привет'),
            array(12, 'привет мир', '', 'привет'),
            array(11, 'привет мир', '', 'приве'), // cut off half character
            array(10, 'привет мир', '', 'приве'), // cut off full character
        );
    }

    public function providerStringsWithPad()
    {
        return array(
            // ASCII
            array(12, 'hello world', '+', 'hello world'),
            array(11, 'hello world', '+', 'hello world'),
            array(10, 'hello world', '+', 'hello wor+'),
            array(6, 'hello world', '+', 'hello+'),
            array(5, 'hello world', '+', 'hell+'),
            array(4, 'hello world', '+', 'hel+'),
            // Unicode
            array(20, 'привет мир', '+', 'привет мир'),
            array(19, 'привет мир', '+', 'привет мир'),
            array(18, 'привет мир', '+', 'привет ми+'),
            array(17, 'привет мир', '+', 'привет м+'),

            array(13, 'привет мир', '+', 'привет+'),
            array(12, 'привет мир', '+', 'приве+'),
            array(11, 'привет мир', '+', 'приве+'),
            array(10, 'привет мир', '+', 'прив+'),
        );
    }

    /**
     * @covers       SMSKrank\Utils\String::limit
     * @dataProvider providerStringsWithPad
     */
    public function testLimitWithPad($length, $string, $pad, $expected)
    {
        $this->assertEquals($expected, String::limit($string, $length, $pad));
    }

//    /**
//     * @covers SMSKrank\Utils\String::limit
//     * @dataProvider providerStringsNoPad
//     */
//    public function testLimitNoPad($length, $string, $pad, $expected) {
//        $this->assertEquals($expected, String::limit($string, $length, $pad));
//    }
}

