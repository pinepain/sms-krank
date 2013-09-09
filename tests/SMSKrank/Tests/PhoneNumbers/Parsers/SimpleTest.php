<?php

namespace SMSKrank\Tests\PhoneNumbers\Parsers;

use SMSKrank\PhoneNumbers\Parsers\PhoneNumberParserInterface;
use SMSKrank\PhoneNumbers\Parsers\Simple;

class SimpleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PhoneNumberParserInterface
     */
    private $parser;

    protected function setUp()
    {
        $this->parser = new Simple();
    }

    public function providerParseSuccess()
    {
        $out = array(
            array('123456789', '123456789'),
            array('123456789', '+1 234 567 89'),
            array('123456789', '+1 (234) 567 89'),
            array('123456789', '+1 (234) 567-89'),
            array('123456789', '+1 (234) 567#89'),
            array('123456789', '+1 (234) 567*89'),
            array('123456789', '+1 (234) 567/89'),
        );

        return $out;
    }

    /**
     * @covers       \SMSKrank\PhoneNumbers\Parsers\Simple::parse
     * @dataProvider providerParseSuccess
     */
    public function testParseSuccess($expected, $given)
    {
        $this->assertEquals($expected, $this->parser->parse($given));
    }

    /**
     * @covers                    \SMSKrank\PhoneNumbers\Parsers\Simple::parse
     *
     * @expectedException \SMSKrank\PhoneNumbers\Parsers\ParserException
     * @expectedExceptionMessage  Empty phone number
     */
    public function testParseFailureOnEmpty()
    {
        $this->parser->parse('');
    }

    /**
     * @covers                    \SMSKrank\PhoneNumbers\Parsers\Simple::parse
     *
     * @expectedException \SMSKrank\PhoneNumbers\Parsers\ParserException
     * @expectedExceptionMessage  Empty phone number
     */
    public function testParseFailureOnAllNonDigits()
    {
        $this->parser->parse('no-digits is same (as empty) #phone /number');
    }
}
