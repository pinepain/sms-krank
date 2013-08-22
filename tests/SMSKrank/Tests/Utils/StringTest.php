<?php

namespace SMSKrank\Tests\Utils;

use SMSKrank\Utils\String;

class StringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \SMSKrank\Utils\String
     */
    protected $s;

    protected function setUp()
    {
        $this->s = new String();
    }

    public function providerStringsWithoutPad()
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
            array(11, 'привет мир', '', 'привет мир'),
            array(10, 'привет мир', '', 'привет мир'),
            array(9, 'привет мир', '', 'привет ми'),
            array(8, 'привет мир', '', 'привет м'),
            array(7, 'привет мир', '', 'привет'),
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
            array(11, 'привет мир', '+', 'привет мир'),
            array(10, 'привет мир', '+', 'привет мир'),
            array(9, 'привет мир', '+', 'привет м+'),
            array(8, 'привет мир', '+', 'привет+'),
            array(7, 'привет мир', '+', 'привет+'),
        );
    }

    public function providerGsmDefaultBasic()
    {
        return array(
            array("@",),
            array("£",),
            array("$",),
            array("¥",),
            array("è",),
            array("é",),
            array("ù",),
            array("ì",),
            array("ò",),
            array("Ç",),
            array("\n",), // LF
            array("Ø",),
            array("ø",),
            array("\r",), // CR
            array("Å",),
            array("å",),
            array("∆",),
            array("_",),
            array("Φ",),
            array("Γ",),
            array("Λ",),
            array("Ω",),
            array("Π",),
            array("Ψ",),
            array("Σ",),
            array("Θ",),
            array("Ξ",),
            array("\x1B",), // \e - ESC
            array("Æ",),
            array("æ",),
            array("ß",),
            array("É",),
            array(" ",), // \x20 - SP, whitespace
            array("!",),
            array("\"",),
            array("#",),
            array("¤",),
            array("%",),
            array("&",),
            array("'",),
            array("(",),
            array(")",),
            array("*",),
            array("+",),
            array(",",),
            array("-",),
            array(".",),
            array("/",),
            array("0",),
            array("1",),
            array("2",),
            array("3",),
            array("4",),
            array("5",),
            array("6",),
            array("7",),
            array("8",),
            array("9",),
            array(":",),
            array(";",),
            array("<",),
            array("=",),
            array(">",),
            array("?",),
            array("¡",),
            array("A",),
            array("B",),
            array("C",),
            array("D",),
            array("E",),
            array("F",),
            array("G",),
            array("H",),
            array("I",),
            array("J",),
            array("K",),
            array("L",),
            array("M",),
            array("N",),
            array("O",),
            array("P",),
            array("Q",),
            array("R",),
            array("S",),
            array("T",),
            array("U",),
            array("V",),
            array("W",),
            array("X",),
            array("Y",),
            array("Z",),
            array("Ä",),
            array("Ö",),
            array("Ñ",),
            array("Ü",),
            array("§",),
            array("¿",),
            array("a",),
            array("b",),
            array("c",),
            array("d",),
            array("e",),
            array("f",),
            array("g",),
            array("h",),
            array("i",),
            array("j",),
            array("k",),
            array("l",),
            array("m",),
            array("n",),
            array("o",),
            array("p",),
            array("q",),
            array("r",),
            array("s",),
            array("t",),
            array("u",),
            array("v",),
            array("w",),
            array("x",),
            array("y",),
            array("z",),
            array("ä",),
            array("ö",),
            array("ñ",),
            array("ü",),
            array("à",),
        );
    }

    public function providerGsmDefaultExtended()
    {
        return array(
            array("\f",), // FF
            array("^",),
            array("{",),
            array("}",),
            array("\\",),
            array("[",),
            array("~",),
            array("]",),
            array("|",),
            array("€",),
        );
    }

    public function providerNonGsm()
    {
        $out = array();

        // http://en.wikipedia.org/wiki/Russian_alphabet, http://stackoverflow.com/q/7461406
        // NOTE: some characters looks like latin but they have totally different code in unicode table
        $cyrillic = array(
            'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у',
            'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я',
            'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У',
            'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я',
        );

        $out += array_chunk($cyrillic, 1);

        return $out;
    }

    public function providerEscapeGsm()
    {
        return array(
            array("[\f^{}\\\\[~]|€", "☮[☮\f☮^☮{☮}☮\\☮\\☮[☮~☮]☮|☮€"),
        );
    }

    public function providerUnescapeGsm()
    {
        return array(
            array("☮[☮\f☮^☮{☮}☮\\☮\\☮[☮~☮]☮|☮€", "[\f^{}\\\\[~]|€"),
        );
    }

    public function providerCleanup()
    {
        return array(
            array('test', 'test'),
            array('test' . substr('t', 1, 1), 'test'),
            array('test' . substr('t', 1, 1) . 'me', 'testme'),
            array('тест', 'тест'),
            array('тест' . substr('т', 1, 1), 'тест'),
            array('тест' . substr('т', 1, 1) . 'мне', 'тестмне'),
        );
    }

    /**
     * @covers       SMSKrank\Utils\String::limit
     * @dataProvider providerStringsWithPad
     */
    public function testLimitWithPad($length, $string, $pad, $expected)
    {
        $this->assertEquals($expected, $this->s->limit($string, $length, $pad));
    }

    /**
     * @covers       SMSKrank\Utils\String::limit
     * @dataProvider providerStringsWithoutPad
     */
    public function testLimitWithoutPad($length, $string, $pad, $expected)
    {
        $this->assertEquals($expected, $this->s->limit($string, $length, $pad));
    }

    /**
     * @covers       SMSKrank\Utils\String::isGSM
     * @dataProvider providerGsmDefaultBasic
     */
    public function testGsmDefaultBasicStrings($string)
    {
        $this->assertTrue($this->s->isGSM($string));
    }

    /**
     * @covers       SMSKrank\Utils\String::isGSM
     * @dataProvider providerGsmDefaultExtended
     */
    public function testGsmDefaultBasicExtended($string)
    {
        $this->assertTrue($this->s->isGSM($string));
    }

    /**
     * @covers       SMSKrank\Utils\String::isGSM
     * @dataProvider providerNonGsm
     */
    public function testNonGsm($string)
    {
        $this->assertFalse($this->s->isGSM($string));
    }

    /**
     * @covers       SMSKrank\Utils\String::escapeGSM
     * @dataProvider providerEscapeGsm
     */
    public function testEscapeGsm($string, $expected)
    {
        $this->assertEquals($expected, $this->s->escapeGSM($string), 'UTF-8');
    }

    /**
     * @covers       SMSKrank\Utils\String::unescapeGSM
     * @dataProvider providerUnescapeGsm
     */
    public function testUnescapeGsm($string, $expected)
    {
        $this->assertEquals($expected, $this->s->unescapeGSM($string), 'UTF-8');
    }

    /**
     * @covers       SMSKrank\Utils\String::cleanup
     * @dataProvider providerCleanup
     */
    public function testCleanup($string, $expected)
    {
        $this->assertEquals($expected, $this->s->cleanup($string), 'UTF-8');
    }
}

