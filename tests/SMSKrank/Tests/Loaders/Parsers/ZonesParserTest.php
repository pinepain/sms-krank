<?php


namespace SMSKrank\Tests\Loaders\Parsers;

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

        $foo_props = array(
            'geo'  => array('country_alpha2' => 'FOO'),
            'code' => array('country' => 1),
        );

        $bar_props = array(
            'geo'  => array('country_alpha2' => 'BAR'),
            'code' => array('country' => 1),
        );

        // override code in zone
        $out[] = array(
            array(
                1 => array(
                    '~' => $foo_props, 5 => array(
                        '~' => array('geo' => array('country_alpha2' => 'BAR'), 'code' => array('country' => 15))
                    )
                )
            ),
            array('1' => 'FOO', '15' => 'BAR'),
            1,
        );

        // plain list of codes and countries
        $out[] = array(
            array(1 => array('~' => $foo_props)),
            array('1' => 'FOO',),
            1,
        );

        // sub-zones loading
        $out[] = array(
            array(1 => array(2 => array('~' => $foo_props))),
            array('12' => '++'),
            1,
            array(
                array('1/2', true, array('~' => $foo_props))
            )
        );

        // nested sub-zones loading
        $out[] = array(
            array(1 => array(2 => array('~' => $foo_props), 3 => array('~' => $bar_props))),
            array('12' => '++', '13' => '++'),
            1,
            array(
                array('1/2', true, array('~' => $foo_props)),
                array('1/3', true, array('~' => $bar_props)),
            )
        );

        // mark specific code as not supported
        $out[] = array(
            array(
                1 =>
                array(
                    '~' => $foo_props,
                    5   => false, //marked as not supported
                )
            ),
            array('1' => 'FOO', '15' => '--'),
            1,
        );

//        // mark code range as not supported
//        $out[] = array(
//            array(
//                1 =>
//                array(
//                    '~' => $foo_props,
//                    5   => false, //marked as not supported
//                    6   => false, //marked as not supported
//                    7   => false, //marked as not supported
//                    8   => false, //marked as not supported
//                )
//            ),
//            array('1' => 'FOO', '15~18' => '--'),
//            1,
//        );

        // add code to previously marked as not supported section
        $out[] = array(
            array(
                1 =>
                array(
                    '~' => $foo_props,
                    // was marked as not supported
                    5   => array(
                        0 => false,
                        1 => false,
                        2 => array(
                            '~' => array(
                                'geo'  => array('country_alpha2' => 'BAR'),
                                'code' => array('country' => 152)
                            )
                        ),
                        3 => false,
                        4 => false,
                        5 => false,
                        6 => false,
                        7 => false,
                        8 => false,
                        9 => false,
                    ),
                )
            ),
            array('1' => 'FOO', '15' => '--', '152' => 'BAR'),
            1,
        );

        return $out;
    }

    public function providerParseFailure()
    {
        $out = array();

        // attempt to load root zone from sub-zone file
        $out[] = array(
            array('1' => '++'),
            1,
            array(),
            'SMSKrank\Loaders\Parsers\Exceptions\ZonesParserException',
            "Root description for zone '1' couldn't be loaded from sub-zones directory",
        );

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

    public function providerExpandCodeListSuccess()
    {
        $out = array();

        $country = 'TEST';

        $out[] = array(array(1 => $country, 2 => $country, 5 => $country), '1, 2, 5', $country);
//        $out[] = array(array(1 => $country, 2 => $country, 5 => $country), '1, 11, 143, 5', $country);// TODO: this one provides ineffective result
        $out[] = array(
            array(1 => array(1 => $country, 2 => $country), 2 => array(5 => $country)), '11, 12, 25', $country
        );

        return $out;
    }

    public function providerExpandCodeListFailure()
    {
        $out     = array();
        $country = 'TEST';

        $out[] = array(
            '1, 2, BAD CODE', $country,
            'SMSKrank\Loaders\Parsers\Exceptions\ZonesParserException',
            "Invalid code list '1, 2, BAD CODE' (bad code 'BAD CODE')"
        );

        return $out;
    }

    /**
     * @covers       \SMSKrank\Loaders\Parsers\ZonesParser::expandCodeList
     *
     * @dataProvider providerExpandCodeListSuccess
     */
    public function testExpandCodeListSuccess($expected, $code, $country)
    {
        $method = new \ReflectionMethod($this->parser, 'expandCodeList');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($this->parser, $code, $country));
    }

    /**
     * @covers       \SMSKrank\Loaders\Parsers\ZonesParser
     *
     * @dataProvider providerParseSuccess
     */
    public function testParseSuccess($expected, $data, $section, $loader_map = array())
    {
        $mock = $this->getMock('SMSKrank\Loaders\LoaderInterface');

        $mock->expects($this->any())
            ->method('load')
            ->will($this->returnValueMap($loader_map));

        $this->assertEquals($expected, $this->parser->parse($data, $section, $mock));
    }

    /**
     * @covers       \SMSKrank\Loaders\Parsers\ZonesParser::expandCodeList
     *
     * @dataProvider providerExpandCodeListFailure
     */
    public function testExpandCodeListFailure($code, $country, $exception_name, $exception_message)
    {
        $method = new \ReflectionMethod($this->parser, 'expandCodeList');
        $method->setAccessible(true);

        $this->setExpectedException($exception_name, $exception_message);

        $method->invoke($this->parser, $code, $country);
    }

    /**
     * @covers       \SMSKrank\Loaders\Parsers\ZonesParser
     *
     * @dataProvider providerParseFailure
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
