<?php

namespace SMSKrank\Utils\Charsets;

use SMSKrank\Utils\Options;

interface CharsetInterface
{
    /**
     * @param array $options Array of options
     */
    public function __construct(array $options = array());


    /**
     * Check whether string contain charset-specific symbols only
     *
     * @param $string
     *
     * @return bool
     */
    public function is($string);

    /**
     * Check whether string contain charset-specific symbols only and throw an exception
     *
     * @param $string
     *
     * @return bool
     * @throws CharsetException When string contains characters that doesn't exists in current charset
     */
    public function check($string);

    /**
     * Remove characters from string that are not in charset
     *
     * @param $string
     *
     * @return mixed
     */
    public function normalize($string);

    /**
     * Measure string length as it takes in SMS message
     *
     * @param $string
     *
     * @return int
     * @throws CharsetException When string has invalid characters
     */
    public function length($string);

    /**
     * Limit string length to given characters number. If padding string given then string will be limited to length - padding length and then padded.
     *
     * @param string $string String to limit
     * @param int    $length Length limit
     * @param string $pad    String to replace cut-off part. Default is empty string. Use '...' for GSM or ASCII strings and "…" for unicode
     *
     * @return string
     */
    public function limit($string, $length, $pad = "");

    /**
     * Remove multiple whitespace characters, trailing spaces and other characters that doesn't affect readability
     *
     * @param $string
     *
     * @return string
     */
    public function compact($string);

    /**
     * Get charset options
     *
     * @return Options
     */
    public function options();
}