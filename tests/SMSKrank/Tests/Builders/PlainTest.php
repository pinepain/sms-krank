<?php


namespace SMSKrank\Tests\Builders;

use SMSKrank\Builders\Plain;

class PlainTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Plain
     */
    protected $builder;

    protected function setUp()
    {
        $this->builder = new Plain();
    }

    public function providerBuildPlain()
    {
        $out = array();

        $plain = 'plain string';
        $out[] = array($plain, $plain, array());
        $out[] = array($plain, $plain, array('extra' => 'ignored'));

        return $out;
    }

    public function providerBuildWithPlaceholders()
    {
        $out = array();

        $pattern = 'test {subject}, just {time}, please';

        $out[] = array('test me, just now, please', $pattern, array('subject' => 'me', 'time' => 'now'));
        $out[] = array('test , just now, please', $pattern, array('time' => 'now'));
        $out[] = array('test , just , please', $pattern, array());
        $out[] = array('test , just , please', $pattern, array('extra' => 'ignored'));

        return $out;
    }

    public function providerBuildConditionsPlain()
    {
        $out = array();

        // one positive condition
        $pattern = 'test{?subject} we\'ve got subject{subject?}, horray!';
        $out[]   = array('test we\'ve got subject, horray!', $pattern, array('subject' => 'me'));
        $out[]   = array('test, horray!', $pattern, array());

        // one negative condition
        $pattern = 'test{!subject} we\'ve got subject{subject!}, horray!';
        $out[]   = array('test, horray!', $pattern, array('subject' => 'me'));
        $out[]   = array('test we\'ve got subject, horray!', $pattern, array());

        // two positive condition
        $pattern = 'test{?subject} we\'ve got subject{subject?}, {?horray}horray!{horray?}';
        $out[]   = array('test we\'ve got subject, horray!', $pattern, array('subject' => 'me', 'horray' => 'aha'));
        $out[]   = array('test we\'ve got subject, ', $pattern, array('subject' => 'me'));
        $out[]   = array('test, horray!', $pattern, array('horray' => 'aha'));
        $out[]   = array('test, ', $pattern, array());

        // one positive and one negative condition
        $pattern = 'test{?subject}, we\'ve got subject, horray!{subject?} me {!subject}. No subjects, sux =({subject!} done';
        $out[]   = array('test, we\'ve got subject, horray! me  done', $pattern, array('subject' => 'me'));
        $out[]   = array('test me . No subjects, sux =( done', $pattern, array());

        // nested conditions
        $pattern = 'test {?condition}condition: {?ok}OK{ok?}{!ok}ERROR{ok!}{condition?}';
        $out[]   = array('test condition: OK', $pattern, array('condition' => 'aha', 'ok' => 'yes'));
        $out[]   = array('test condition: ERROR', $pattern, array('condition' => 'aha', 'ok' => false));

        return $out;
    }


    /**
     * @covers       \SMSKrank\Builders\Plain
     *
     * @dataProvider providerBuildPlain
     *
     * @param       $expected
     * @param       $pattern
     * @param array $arguments
     */
    public function testBuildPlain($expected, $pattern, array $arguments)
    {
        $this->assertEquals($expected, $this->builder->build($pattern, $arguments));
    }

    /**
     * @covers       \SMSKrank\Builders\Plain
     *
     * @dataProvider providerBuildWithPlaceholders
     *
     * @param       $expected
     * @param       $pattern
     * @param array $arguments
     */
    public function testBuildWithPlaceholders($expected, $pattern, array $arguments)
    {
        $this->assertEquals($expected, $this->builder->build($pattern, $arguments));
    }

    /**
     * @covers       \SMSKrank\Builders\Plain
     *
     * @dataProvider providerBuildConditionsPlain
     *
     * @param       $expected
     * @param       $pattern
     * @param array $arguments
     */
    public function testBuildConditionsPlain($expected, $pattern, array $arguments)
    {
        $this->assertEquals($expected, $this->builder->build($pattern, $arguments));
    }
}
