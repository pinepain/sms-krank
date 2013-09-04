<?php


namespace SMSKrank\Tests\Loaders\Parsers;

use SMSKrank\Loaders\Parsers\MapsParser;
use SMSKrank\Loaders\Parsers\ParserInterface;

class MapsParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ParserInterface
     */
    protected $parser;

    protected function setUp()
    {
        $this->parser = new MapsParser();
    }

    public function producerParseSuccess()
    {
        $out = array();

        $section = 'FOO-COUNTRY';

        $loader = $this->getMock('SMSKrank\Loaders\LoaderInterface');
        $loader->expects($this->any())
            ->method('has')
            ->will($this->returnValue(true));

        // single gate, expand of 'any' filter
        $out[] = array(
            array($section => array('Gate' => null)),
            array('Gate' => 'any'),
            $section,
            $loader,
        );

        // single gate, regular filter
        $out[] = array(
            array($section => array('Gate' => array('property' => 'value'))),
            array('Gate' => array('property' => 'value')),
            $section,
            $loader,
        );

        // multiple gate, mixed filters
        $out[] = array(
            array($section => array('Gate' => array('property' => 'value'), 'Failover' => null)),
            array('Gate' => array('property' => 'value'), 'Failover' => 'any'),
            $section,
            $loader,
        );

        return $out;
    }

    public function producerParseFailure()
    {
        $out = array();

        $section = 'FOO-COUNTRY';

        $loader_true = $this->getMock('SMSKrank\Loaders\LoaderInterface');
        $loader_true->expects($this->any())
            ->method('has')
            ->will($this->returnValue(true));

        $loader_false = $this->getMock('SMSKrank\Loaders\LoaderInterface');
        $loader_false->expects($this->any())
            ->method('has')
            ->will($this->returnValue(false));

        // empty map
        $out[] = array(
            array(),
            $section,
            $loader_true,
            'SMSKrank\Loaders\Parsers\Exceptions\MapsParserException',
            "Map 'FOO-COUNTRY' is empty"
        );

        // invalid filter value
        $out[] = array(
            array('GateName' => 'should be an array'),
            $section,
            $loader_true,
            'SMSKrank\Loaders\Parsers\Exceptions\MapsParserException',
            "Map 'FOO-COUNTRY' filter for gate 'GateName' invalid"
        );

        // invalid filter value
        $out[] = array(
            array('GateName' => 'any'),
            $section,
            $loader_false,
            'SMSKrank\Loaders\Parsers\Exceptions\MapsParserException',
            "Gateway 'GateName' from 'FOO-COUNTRY' map doesn't exists"
        );

        return $out;
    }

    /**
     * @covers       \SMSKrank\Loaders\Parsers\MapsParser::parse
     * @dataProvider producerParseSuccess
     */
    public function testParseSuccess($expected, $data, $section, $loader)
    {
        $this->assertEquals($expected, $this->parser->parse($data, $section, $loader));
    }

    /**
     * @covers       \SMSKrank\Loaders\Parsers\MapsParser::parse
     * @dataProvider producerParseFailure
     */
    public function testParseFailure($data, $section, $loader, $exception_name, $exception_message)
    {
        $this->setExpectedException($exception_name, $exception_message);

        $this->parser->parse($data, $section, $loader);
    }

}
