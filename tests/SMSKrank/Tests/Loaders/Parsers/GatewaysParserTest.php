<?php

namespace SMSKrank\Tests\Loaders\Parsers;

use SMSKrank\Loaders\Parsers\GatewaysParser;
use SMSKrank\Loaders\Parsers\ParserInterface;

class GatewaysParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ParserInterface
     */
    protected $parser;

    protected function setUp()
    {
        $this->parser = new GatewaysParser();
    }

    public function producerGetGateArgumentsSuccess()
    {
        $out = array();

        // no constructor, no args
        $out[] = array(array(), '\SMSKrank\Helpers\Gateways\Valid\NoConstructor', array());
        // no constructor, some args given
        $out[] = array(array(), '\SMSKrank\Helpers\Gateways\Valid\NoConstructor', array('foo' => 'bar'));

        // all args required and provided in valid order
        $out[] = array(
            array(1, 2, 3),
            '\SMSKrank\Helpers\Gateways\Valid\ConstructorWithAllRequired',
            array('foo' => 1, 'bar' => 2, 'baz' => 3)
        );

        // all args required and provided in random order
        $out[] = array(
            array(1, 2, 3),
            '\SMSKrank\Helpers\Gateways\Valid\ConstructorWithAllRequired',
            array('baz' => 3, 'foo' => 1, 'bar' => 2,)
        );

        // all arguments has default values
        $out[] = array(
            array(1, 2, 3),
            '\SMSKrank\Helpers\Gateways\Valid\ConstructorWithAllDefault',
            array()
        );

        // all arguments has default values, try to override some some of them
        $out[] = array(
            array(1, array(), 3),
            '\SMSKrank\Helpers\Gateways\Valid\ConstructorWithAllDefault',
            array('bar' => array())
        );

        // some arguments has default values
        $out[] = array(
            array(11, 22, 3, 4),
            '\SMSKrank\Helpers\Gateways\Valid\ConstructorWithSomeDefault',
            array('foo' => 11, 'bar' => 22)
        );

        // some arguments has default values, override them
        $out[] = array(
            array(1, 2, 12345, 4),
            '\SMSKrank\Helpers\Gateways\Valid\ConstructorWithSomeDefault',
            array('baz' => 12345, 'foo' => 1, 'bar' => 2)
        );

        return $out;
    }

    public function producerGetGateArgumentsFailure()
    {
        $out        = array();
        $gate_class = '\SMSKrank\Helpers\Gateways\DoesNotImplement';

        // doesnt implement sender interface
        $out[]      = array(
            $gate_class,
            array('foo' => 1, 'bar' => 2),
            'SMSKrank\Loaders\Parsers\Exceptions\GatewaysParserException',
            "Gateway class '{$gate_class}' doesn't implement interface"
        );
        $gate_class = '\SMSKrank\Helpers\Gateways\Valid\ConstructorWithAllRequired';

        // missed argument
        $out[] = array(
            $gate_class,
            array('foo' => 1, 'bar' => 2),
            'SMSKrank\Loaders\Parsers\Exceptions\GatewaysParserException',
            "Gateway class '{$gate_class}' constructor argument 'baz' missed"
        );
        $out[] = array(
            $gate_class,
            array('foo' => 1, 'baz' => 3),
            'SMSKrank\Loaders\Parsers\Exceptions\GatewaysParserException',
            "Gateway class '{$gate_class}' constructor argument 'bar' missed"
        );

        $gate_class = '\SMSKrank\Helpers\Gateways\Valid\ConstructorWithSomeDefault';

        // some arguments has default values, but no arguments given at all
        $out[] = array(
            $gate_class,
            array(),
            'SMSKrank\Loaders\Parsers\Exceptions\GatewaysParserException',
            "Gateway class '{$gate_class}' constructor argument 'foo' missed"
        );
        // some arguments has default values, but not all required values given
        $out[] = array(
            $gate_class,
            array('foo' => 11),
            'SMSKrank\Loaders\Parsers\Exceptions\GatewaysParserException',
            "Gateway class '{$gate_class}' constructor argument 'bar' missed"
        );
        // some arguments has default values, but value for default one provided only
        $out[] = array(
            $gate_class,
            array('baz' => 33),
            'SMSKrank\Loaders\Parsers\Exceptions\GatewaysParserException',
            "Gateway class '{$gate_class}' constructor argument 'foo' missed"
        );

        return $out;
    }

    public function producerParseSuccess()
    {
        $out = array();

        $section = 'section-ignored';

        $out[] = array(
            array(
                'class'   => '\SMSKrank\Helpers\Gateways\Valid\ConstructorWithAllRequired',
                'args'    => array('mocked'),
                'options' => array()
            ),
            array(
                'class'   => '\SMSKrank\Helpers\Gateways\Valid\ConstructorWithAllRequired',
                'args'    => array('foo' => 1, 'bar' => 2, 'baz' => 3),
                'options' => array()
            ),
            $section,
            array('mocked')
        );

        // default value for options
        $out[] = array(
            array(
                'class'   => '\SMSKrank\Helpers\Gateways\Valid\ConstructorWithAllRequired',
                'args'    => array('mocked'),
                'options' => array()
            ),
            array(
                'class' => '\SMSKrank\Helpers\Gateways\Valid\ConstructorWithAllRequired',
                'args'  => array('foo' => 1, 'bar' => 2, 'baz' => 3),
            ),
            $section,
            array('mocked')
        );

        // default value for arguments
        $out[] = array(
            array(
                'class'   => '\SMSKrank\Helpers\Gateways\Valid\ConstructorWithAllRequired',
                'args'    => array('mocked'),
                'options' => array()
            ),
            array(
                'class' => '\SMSKrank\Helpers\Gateways\Valid\ConstructorWithAllRequired',
            ),
            $section,
            array('mocked')
        );

        return $out;
    }

    public function producerParseFailure()
    {
        $out = array();

        $section = 'section-ignored';
        // invalid options type
        $out[] = array(
            array(
                'class'   => '\SMSKrank\Helpers\Gateways\Valid\ConstructorWithAllRequired',
                'args'    => array('foo' => 1, 'bar' => 2, 'baz' => 3),
                'options' => 'invalid type'
            ),
            $section,
            'SMSKrank\Loaders\Parsers\Exceptions\GatewaysParserException',
            "Gateway 'section-ignored' options should be array",
        );

        // invalid args type
        $out[] = array(
            array(
                'class' => '\SMSKrank\Helpers\Gateways\Valid\ConstructorWithAllRequired',
                'args'  => 'invalid type',
            ),
            $section,
            'SMSKrank\Loaders\Parsers\Exceptions\GatewaysParserException',
            "Gateway 'section-ignored' arguments should be array",
        );

        // missed class
        $out[] = array(
            array(
                'args' => array('mocked'),
            ),
            $section,
            'SMSKrank\Loaders\Parsers\Exceptions\GatewaysParserException',
            "Gateway 'section-ignored' class missed",
        );

        // non-existent class
        $out[] = array(
            array(
                'class' => 'NonExistentClass',
                'args'  => array('mocked'),
            ),
            $section,
            'SMSKrank\Loaders\Parsers\Exceptions\GatewaysParserException',
            "Gateway 'section-ignored' class 'NonExistentClass' doesn't exists",
        );

        return $out;
    }

    /**
     * @covers       \SMSKrank\Loaders\Parsers\GatewaysParser::getGateArguments
     * @dataProvider producerGetGateArgumentsSuccess
     */
    public function testGetGateArgumentsSuccess($expected, $class, $arguments)
    {
        $method = new \ReflectionMethod($this->parser, 'getGateArguments');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($this->parser, $class, $arguments));
    }

    /**
     * @covers       \SMSKrank\Loaders\Parsers\GatewaysParser::getGateArguments
     * @dataProvider producerGetGateArgumentsFailure
     */
    public function testGetGateArgumentsFailure($class, $arguments, $exception_name, $exception_message)
    {
        $method = new \ReflectionMethod($this->parser, 'getGateArguments');
        $method->setAccessible(true);

        $this->setExpectedException($exception_name, $exception_message);

        $method->invoke($this->parser, $class, $arguments);
    }

    /**
     * @covers       \SMSKrank\Loaders\Parsers\GatewaysParser::parse
     * @dataProvider producerParseSuccess
     */
    public function testParseSuccess($expected, $data, $section, $mock_result)
    {
        $loader = $this->getMock('SMSKrank\Loaders\LoaderInterface');

        $parser = $this->getMock(get_class($this->parser), array('getGateArguments'));

        $parser->expects($this->any())
            ->method('getGateArguments')
            ->will($this->returnValue($mock_result));

        $this->assertEquals($expected, $parser->parse($data, $section, $loader));
    }

    /**
     * @covers       \SMSKrank\Loaders\Parsers\GatewaysParser::parse
     * @dataProvider producerParseFailure
     */
    public function testParseFailure($data, $section, $exception_name, $exception_message)
    {
        $loader = $this->getMock('SMSKrank\Loaders\LoaderInterface');

        $this->setExpectedException($exception_name, $exception_message);

        $this->parser->parse($data, $section, $loader);
    }

}
