<?php

namespace SMSKrank\Tests\PhoneNumbers\Parsers;

use SMSKrank\PhoneNumbers\Parsers\PhoneNumberParserInterface;
use SMSKrank\PhoneNumbers\Parsers\PhoneWord;

class PhoneWordTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PhoneNumberParserInterface
     */
    private $parser;

    protected function setUp()
    {
        $this->parser = new PhoneWord();
    }

    public function providerParseSuccess()
    {
        $out = array(
            array('18006927753', '1-800-69-27753'),
            array('18006927753', '1-800-MY-APPLE'),
            array('18006927753', '1-800-my-apple'),
            array('18006927753', '1-800-My-Apple'),

            // in fact, it is "MY-IPHONE". but we skip E letter here to fit phone number length
            // TODO: deal with it (800) MY-IPHONE but (800) 694-7466 (instead of (800) 694-7466-3, cause 3 will overflow number length
            array('18006947466', '1-800-69-47466'),
            array('18006947466', '1-800-MY-IPHON'),
            array('18006947466', '1-800-my-iphon'),
            array('18006947466', '1-800-My-Iphon'),
        );

        return $out;
    }

    /**
     * @covers       \SMSKrank\PhoneNumbers\Parsers\PhoneWord::parse
     * @covers       \SMSKrank\PhoneNumbers\Parsers\PhoneWord::translatePhoneWorlds
     * @dataProvider providerParseSuccess
     */
    public function testParseSuccess($expected, $given)
    {
        $this->assertEquals($expected, $this->parser->parse($given));
    }

}
