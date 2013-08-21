<?php

namespace SMSKrank\Tests;

use SMSKrank\Helpers\Utils\PlaceholdersBuilder;
use SMSKrank\Message;

class MessageTest extends \PHPUnit_Framework_TestCase
{

    public function providerBuilder()
    {
        $args      = new \stdClass();
        $args->foo = 'foo val';
        $args->bar = 'bar val';

        return array(
            array('{foo}, {bar}', 'foo val, bar val', array('foo' => 'foo val', 'bar' => 'bar val')),
            array('{foo}, {bar}', 'foo val, {bar}', array('foo' => 'foo val')),
            array('{foo}, {bar}', '{foo}, {bar}', array()),
            array('{foo}, {bar}', '{foo}, {bar}', null),
            array('{foo}, {bar}', 'foo val, bar val', $args),
        );
    }

    public function providerLongMessages()
    {
        return array(
            array('long message is here', 4, '', 4),
            array('long message is here', 5, '', 4),
            array('long message is here', 6, '', 6),
            array('long message is here', 4, '...', 4),
            array('long message is here', 5, '...', 5),
            array('long message is here', 6, '...', 6),
        );
    }

    public function providerMessage()
    {
        $out = array(
            // 7-bit encoding
            array('short message', 'short message'),
            // we do not handle spaces removal due to ambiguity with rtl languages, so no trim
            array('   short message', 'short message'),
            array("\t\t\tshort message", 'short message'),
            array("\n\n\nshort message", 'short message'),
            array("\r\r\rshort message", 'short message'),
            array('short message   ', 'short message'),
            array("short message\t", 'short message'),
            array("short message\n", 'short message'),
            array("short message\r", 'short message'),
            array("\tshort message", 'short message'),
            array("\nshort message", 'short message'),
            array("\rshort message", 'short message'),
            array("short\nmessage", 'short message'),
            array("short\n\nmessage", 'short message'),
            array("short\tmessage", 'short message'),
            array("short\rmessage", 'short message'),
            array(str_repeat('test', 40), null),
            array(str_repeat('test', 40) . '+', null),
//            // 16-bit encodings
            array('тест', null),
            array(str_repeat('тест', 20), null),
            array('اختباررر', null),
            array(str_repeat('اختباررر', 10), null),
            array('מבחן', null),
            array(str_repeat('מבחן', 20), null),
            array('测试', null),
            array(str_repeat('测试', 27), null),
            array('데모', null),
            array(str_repeat('데모', 27), null),
        );

        foreach ($out as &$o) {
            if (!$o[1]) {
                $o[1] = $o[0];
            }
        }

        return $out;
    }

    /**
     * @covers       \SMSKrank\Message::__construct
     * @covers       \SMSKrank\Message::getText
     *
     * @dataProvider providerMessage
     */
    public function testGetTextFull($message, $expected)
    {
        $obj = new Message($message);
        $obj->options()->set('compact', false);
        $obj->options()->set('max-length', false);

        $this->assertEquals($message, $obj->getText(false));
    }

    /**
     * @covers       \SMSKrank\Message::__construct
     * @covers       \SMSKrank\Message::getText
     *
     * @dataProvider providerMessage
     */
    public function testGetTextCompact($message, $compact)
    {
        $obj = new Message($message);
        $obj->options()->set('max-length', false);

        $this->assertEquals($compact, $obj->getText());
    }

    /**
     * @covers       \SMSKrank\Message::__construct
     * @covers       \SMSKrank\Message::getPattern
     *
     * @dataProvider providerBuilder
     */
    public function testGetPattern($message, $compiled, $arguments)
    {
        $obj = new Message($message, $arguments);
        $obj->options()->set('max-length', false);

        $this->assertEquals($message, $obj->getPattern());
    }

    /**
     * @covers       \SMSKrank\Message::__construct
     * @covers       \SMSKrank\Message::getArguments
     *
     * @dataProvider providerBuilder
     */
    public function testGetArguments($message, $compiled, $arguments)
    {
        $obj = new Message($message, $arguments);
        $obj->options()->set('max-length', false);

        $this->assertEquals($arguments, $obj->getArguments());
    }

    /**
     * @covers       \SMSKrank\Message::__construct
     * @covers       \SMSKrank\Message::getText
     *
     * @dataProvider providerBuilder
     */
    public function testGetTextBuilt($message, $compiled, $arguments)
    {
        $builder = new PlaceholdersBuilder();
        $obj     = new Message($message, $arguments, $builder);
        $obj->options()->set('max-length', false);

        $this->assertEquals($compiled, $obj->getText());
    }

    /**
     * @covers       \SMSKrank\Message::__construct
     * @covers       \SMSKrank\Message::getText
     *
     * @dataProvider providerLongMessages
     */
    public function testGetTextMaxLength($message, $max_length, $pad, $output_length)
    {
        $obj = new Message($message);
        $obj->options()->set('compact', false);
        $obj->options()->set('max-length', $max_length);
        $obj->options()->set('max-length-pad', $pad);

        $this->assertEquals($output_length, strlen($obj->getText()));
    }

    /**
     * @covers \SMSKrank\Message::options
     */
    public function testOptions()
    {
        $obj = new Message('');

        $this->assertInstanceOf('SMSKrank\Utils\Options', $obj->options());
    }

}
