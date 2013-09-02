<?php

namespace SMSKrank\Tests\Utils\Charsets;

use SMSKrank\Utils\Charsets\Gsm;

class GsmTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Gsm;
     */
    private $c;

    protected function setUp()
    {
        $this->c = new Gsm();
    }

    public function providerIsTrue()
    {
        $out = array();

        $ascii_control = "\x0A\x0C\x0D\x1B";
        $ascii_chars   = ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_abcdefghijklmnopqrstuvwxyz{|}~';

        $gsm_control = "\x0C\x0D"; // NOTE: don't know what to do with CR2, SS2
        $gsm_unicode = "Δ¡¿£Φ¥ΓèΛ¤éΩùΠìΨòΣÇΘØΞÄäøÆÖöæÑñÅßÜüåÉ§à€";

        $valid = $ascii_control . $ascii_chars . $gsm_control . $gsm_unicode;

        foreach (unicode_str_split($valid) as $c) {
            $out[] = array($c);
        }

        $out[] = array($ascii_control);
        $out[] = array($ascii_chars);
        $out[] = array($gsm_control);
        $out[] = array($gsm_unicode);

        return $out;
    }

    public function providerIsFalse()
    {
        $out = array();

        $ascii_control      = "\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0B\x0E\x0F\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1C\x1D\x1E\x1F\x7F"; // NOTE: we ignore \x00
        $ascii_grave_accent = '`';
        $gsm_control        = ""; // NOTE: don't know what to do with CR2, SS2
        $random_unicode     = "“й";
        $invalid            = $ascii_control . $ascii_grave_accent . $gsm_control . $random_unicode;

        foreach (unicode_str_split($invalid) as $c) {
            $out[] = array($c);
        }

        $out[] = array($ascii_control);
        $out[] = array($ascii_grave_accent);
//        $out[] = array($gsm_control);
        $out[] = array($invalid);

        return $out;
    }

    public function providerNormalize()
    {
        $out      = array();
        $expected = 'lorΞm ipsum do¡or sit amet';

        $out[] = array($expected, 'lorΞm ipsum do¡or sit amet');
        $out[] = array($expected, '   lorΞm ipsum do¡or sit amet');
        $out[] = array($expected, 'lorΞm ipsum do¡or sit amet    ');
        $out[] = array($expected, 'ЪlorΞm Юips`um Йdo¡or ЯsitЫ amet');

        return $out;
    }

    public function providerLength()
    {
        $out = array();

        $ascii_control_one = "\x0A\x0D\x1B";
        $ascii_chars_one   = ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz_';
        $gsm_unicode_one   = "Δ¡¿£Φ¥ΓèΛ¤éΩùΠìΨòΣÇΘΞØÄäøÆÖöæÑñÅßÜüåÉ§à";

        $ascii_control_two = "\x0C";
        $ascii_chars_two   = '[\]^{|}~';
        $gsm_unicode_two   = '€';

        foreach (unicode_str_split($ascii_control_one . $ascii_chars_one . $gsm_unicode_one) as $c) {
            $out[] = array(1, $c);
        }

        foreach (unicode_str_split($ascii_control_two . $ascii_chars_two . $gsm_unicode_two) as $c) {
            $out[] = array(2, $c);
        }

        $out[] = array(26, 'lorΞm ipsum do¡or sit amet');
        $out[] = array(28, '(lorΞm ipsum do¡or sit amet)');
        $out[] = array(30, ' (lorΞm ipsum do¡or sit amet) ');

        return $out;
    }

    public function providerLimit()
    {
        $out = array();

        $given = 'tΞst';

        $out[] = array('tΞst', $given, 4, '...');
        $out[] = array('tΞs', $given, 3, '...');
        $out[] = array('t', $given, 1, '...');

        $given = 'tΞst me please';

        $out[] = array('t...', $given, 4, '...');
        $out[] = array('tΞs', $given, 3, '...');
        $out[] = array('t', $given, 1, '...');

        $out[] = array('tΞst...', $given, 7, null); // tΞst default fill

        $given = 'lorΞm ipsum do¡or sit amet';

        $out[] = array('lorΞm ipsum do¡or sit amet', $given, 100, '...');
        $out[] = array('lorΞm ipsum do¡or sit amet', $given, 100, '');

        $out[] = array('lorΞm ipsum do¡or sit amet', '    ' . $given, 100, '...');
        $out[] = array('lorΞm ipsum do¡or sit amet', '    ' . $given, 100, '');

        $out[] = array('lorΞm ipsum do¡or sit amet', '    ' . $given . '    ', 100, '...');
        $out[] = array('lorΞm ipsum do¡or sit amet', '    ' . $given . '    ', 100, '');

        $out[] = array('lorΞm ipsum do¡or sit amet', $given . '    ', 100, '...');
        $out[] = array('lorΞm ipsum do¡or sit amet', $given . '    ', 100, '');

        $out[] = array('lorΞm ipsum do¡or sit amet', $given, 26, '...');
        $out[] = array('lorΞm ipsum do¡or sit amet', $given, 26, '');

        $out[] = array('lorΞm ipsum do¡or sit amet', '    ' . $given, 26, '...');
        $out[] = array('lorΞm ipsum do¡or sit amet', '    ' . $given, 26, '');

        $out[] = array('lorΞm ipsum do¡or sit amet', '    ' . $given . '    ', 26, '...');
        $out[] = array('lorΞm ipsum do¡or sit amet', '    ' . $given . '    ', 26, '');

        $out[] = array('lorΞm ipsum do¡or sit amet', $given . '    ', 26, '...');
        $out[] = array('lorΞm ipsum do¡or sit amet', $given . '    ', 26, '');

        $out[] = array('lorΞm ipsum do¡or sit...', $given, 25, '...');
        $out[] = array('lorΞm ipsum do¡or sit ame', $given, 25, '');

        $out[] = array('lorΞm ipsum do¡or sit...', '    ' . $given, 25, '...');
        $out[] = array('lorΞm ipsum do¡or sit ame', '    ' . $given, 25, '');

        $out[] = array('lorΞm ipsum do¡or sit...', '    ' . $given . '    ', 25, '...');
        $out[] = array('lorΞm ipsum do¡or sit ame', '    ' . $given . '    ', 25, '');

        $out[] = array('lorΞm ipsum do¡or sit...', $given . '    ', 25, '...');
        $out[] = array('lorΞm ipsum do¡or sit ame', $given . '    ', 25, '');

        $out[] = array('lorΞm ipsum do¡or...', $given, 21, '...');
        $out[] = array('lorΞm ipsum do¡or sit', $given, 21, '');

        $out[] = array('lorΞm ipsum do¡or s...', $given, 22, '...');
        $out[] = array('lorΞm ipsum do¡or sit', $given, 22, '');

        $given = '(lorΞm ipsum do¡or sit amet)';

        $out[] = array('(lorΞm ipsum do¡or sit amet)', $given, 100, '...');
        $out[] = array('(lorΞm ipsum do¡or sit amet)', $given, 100, '');

        $out[] = array('(lorΞm ipsum do¡or sit amet)', $given, 28, '...');
        $out[] = array('(lorΞm ipsum do¡or sit amet)', $given, 28, '');

        $out[] = array('(lorΞm ipsum do¡or sit a...', $given, 27, '...');
        $out[] = array('(lorΞm ipsum do¡or sit amet', $given, 27, '');

        $out[] = array('(lorΞm ipsum do¡or sit...', $given, 26, '...');
        $out[] = array('(lorΞm ipsum do¡or sit ame', $given, 26, '');

        $given = '~(lorΞm ipsum do¡or sit amet)';

        $out[] = array('~(lorΞm ipsum do¡or sit amet)', $given, 100, '...');
        $out[] = array('~(lorΞm ipsum do¡or sit amet)', $given, 100, '');

        $out[] = array('~(lorΞm ipsum do¡or sit amet)', $given, 30, '...');
        $out[] = array('~(lorΞm ipsum do¡or sit amet)', $given, 30, '');

        $out[] = array('~(lorΞm ipsum do¡or sit a...', $given, 29, '...');
        $out[] = array('~(lorΞm ipsum do¡or sit amet', $given, 29, '');

        $out[] = array('~(lorΞm ipsum do¡or sit...', $given, 28, '...');
        $out[] = array('~(lorΞm ipsum do¡or sit ame', $given, 28, '');

        $out[] = array('~(lorΞm ipsum do¡or sit...', $given, 27, '...');
        $out[] = array('~(lorΞm ipsum do¡or sit am', $given, 27, '');

        $out[] = array('lorΞm ipsum do¡or sit amet', 'lorΞm ipsum do¡or sit amet', 26, '~');
        $out[] = array('lorΞm ipsum do¡or sit a~', 'lorΞm ipsum do¡or sit amet', 25, '~');

        $out[] = array('(lorΞm ipsum do¡or sit amet)', '(lorΞm ipsum do¡or sit amet)', 28, '~');
        $out[] = array('(lorΞm ipsum do¡or sit am~', '(lorΞm ipsum do¡or sit amet)', 27, '~');

        $out[] = array('~(lorΞm ipsum do¡or sit amet)', '~(lorΞm ipsum do¡or sit amet)', 30, '~');
        $out[] = array('~(lorΞm ipsum do¡or sit am~', '~(lorΞm ipsum do¡or sit amet)', 29, '~');

        return $out;
    }

    public function providerCompact()
    {
        $out = array();

        $expected = 'lorΞm ipsum do¡or sit amet';

        $out[] = array($expected, "lorΞm ipsum do¡or sit amet");
        $out[] = array($expected, "    lorΞm ipsum do¡or sit amet");
        $out[] = array($expected, "    lorΞm ipsum do¡or sit amet    ");
        $out[] = array($expected, "lorΞm ipsum do¡or sit amet    ");
        $out[] = array("", "  ");
        $out[] = array("", "\x0A");
        $out[] = array("", "\x0C");
        $out[] = array("", "\x0D");
        $out[] = array("", "\x1B");
        $out[] = array("[ ]", "[ ]");
        $out[] = array("[ ]", "[\r]");
        $out[] = array("[ ]", "[\r]");
        $out[] = array("[ ]", "[\x1B]");

        return $out;
    }

    /**
     * @covers       SMSKrank\Utils\Charsets\Gsm::options
     */
    public function testOptions()
    {
        $this->assertInstanceOf('\SMSKrank\Utils\Options', $this->c->options());
    }

    /**
     * @covers       SMSKrank\Utils\Charsets\Gsm::__construct
     */
    public function testConstructor()
    {
        $this->assertEquals(
            array(
                'str-pad'    => '...',
                'len-single' => 160,
                'len-chunks' => 153,
            ),
            $this->c->options()->all()
        );
    }

    /**
     * @covers       SMSKrank\Utils\Charsets\Gsm::is
     *
     * @dataProvider providerIsTrue
     */
    public function testIsTrue($string)
    {
        $this->assertTrue($this->c->is($string));
    }

    /**
     * @covers       SMSKrank\Utils\Charsets\Gsm::is
     *
     * @dataProvider providerIsFalse
     */
    public function testIsFalse($string)
    {
        $this->assertFalse($this->c->is($string));
    }

    /**
     * @covers       SMSKrank\Utils\Charsets\Gsm::check
     *
     * @dataProvider providerIsTrue
     */
    public function testCheckOk($string)
    {
        $this->c->check($string);
    }

    /**
     * @covers                   SMSKrank\Utils\Charsets\Gsm::check
     *
     * @dataProvider             providerIsFalse
     *
     * @expectedException        \SMSKrank\Utils\Charsets\CharsetException
     * @expectedExceptionMessage String contains invalid characters
     */
    public function testCheckFailure($string)
    {
        $this->c->check($string);
    }

    /**
     * @covers       SMSKrank\Utils\Charsets\Gsm::normalize
     *
     * @dataProvider providerNormalize
     */
    public function testNormalize($expected, $given)
    {
        $this->assertSame($expected, $this->c->normalize($given));
    }

    /**
     * @covers       SMSKrank\Utils\Charsets\Gsm::length
     * @covers       SMSKrank\Utils\Charsets\Gsm::escape
     *
     * @dataProvider providerLength
     */
    public function testLength($expected, $given)
    {
        $this->assertSame($expected, $this->c->length($given));
    }

    /**
     * @covers       SMSKrank\Utils\Charsets\Gsm::limit
     * @covers       SMSKrank\Utils\Charsets\Gsm::escape
     * @covers       SMSKrank\Utils\Charsets\Gsm::unescape
     *
     * @dataProvider providerLimit
     */
    public function testLimit($expected, $given, $length, $pad)
    {
        $this->assertSame($expected, $this->c->limit($given, $length, $pad));
    }

    /**
     * @covers       SMSKrank\Utils\Charsets\Gsm::compact
     *
     * @dataProvider providerCompact
     */
    public function testCompact($expected, $given)
    {
        $this->assertSame($expected, $this->c->compact($given));
    }

}
