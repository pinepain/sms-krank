<?php


namespace SMSSKrank\Tests\Loaders\Parsers;

use SMSKrank\Loaders\Parsers\ParserInterface;
use SMSKrank\Loaders\Parsers\ZonesParser;


class ZonesParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ParserInterface
     */
    protected $parser;

    protected function setUp()
    {
        $this->parser = new ZonesParser();
    }

    public function providerParseSuccess()
    {
        $out = array();

        // plain list of codes and countries
        $out[] = array(
            array(
                1 => array(
                    '~' => array(
                        'geo'  => array('country_alpha2' => 'FOO'),
                        'code' => array('country' => 1),
                    )
                ),
            ),
            array(
                '1' => 'FOO',
            ),
            1,
            array()
        );

        // nested loading
        $out[] = array(
            array(
                1 => array(
                    '~' => array(
                        'geo'  => array('country_alpha2' => 'FOO'),
                        'code' => array('country' => 1),
                    )
                ),
            ),
            array(
                '1 23' => array(
                    '45' => array(
                        '67' => '++'
                    ),
                ),
            ),
            1,
            array(
                '1/root' => array(
                    'geo'  => array('country_alpha2' => 'FOO'),
                    'code' => array('country' => 1),
                ),
            )
        );

        return $out;
    }

    public function providerParseFailure()
    {
        $out = array();

        // zones mixing in data, we don't care(?) TODO: fix it, possible collision?
        $out[] = array(
            array(
                '1' => 'FOO',
                '2' => 'BAR',
            ),
            1,
            array(),
            'SMSKrank\Loaders\Parsers\Exceptions\ZonesParserException',
            "Root zone '1' possible collision (zones present: 1, 2)",
        );

        return $out;
    }

    /**
     * @covers       \SMSKrank\Loaders\Parsers\ZonesParser
     *
     * @dataProvider providerParseSuccess
     *
     * @param $expected
     * @param $data
     * @param $section
     * @param $loader_map
     */
    public function testParseSuccess($expected, $data, $section, $loader_map)
    {
        $mock = $this->getMock('SMSKrank\Loaders\LoaderInterface');

        $mock->expects($this->any())
            ->method('load')
            ->will($this->returnValueMap($loader_map));

        $this->assertEquals($expected, $this->parser->parse($data, $section, $mock));
    }


    /**
     * @covers       \SMSKrank\Loaders\Parsers\ZonesParser
     *
     * @dataProvider providerParseFailure
     *
     * @param $data
     * @param $section
     * @param $loader_map
     * @param $exception_name
     * @param $exception_message
     */
    public function testParseFailure($data, $section, $loader_map, $exception_name, $exception_message)
    {
        $this->setExpectedException($exception_name, $exception_message);

        $mock = $this->getMock('SMSKrank\Loaders\LoaderInterface');

        $mock->expects($this->any())
            ->method('load')
            ->will($this->returnValueMap($loader_map));

        $this->parser->parse($data, $section, $mock);
    }


}
