<?php

namespace SMSKrank\Utils\Charsets;

// NOTE: some characters in UTF-8 takes 3 bytes, but in fact sms text may be GSM or UCS-2

// GSM 7-bit alphabet support is mandatory for GSM handsets and network elements,[38] but characters in languages such
// as Arabic, Chinese, Korean, Japanese, or Cyrillic alphabet languages (e.g., Russian, Serbian, Bulgarian, etc.) must
// be encoded using the 16-bit UCS-2 character encoding (see Unicode). Routing data and other metadata is additional to
//the payload size.

use SMSKrank\Utils\Options;

class Unicode implements CharsetInterface
{
    protected $options;

    public function __construct(array $options)
    {
        $this->options()->set($options);

        $this->options()->set(
            array(
                'str-pad'    => '…',
                'len-single' => 70,
                'len-chunks' => 67,
            ),
            false
        );
    }

    /**
     * Check whether string contain charset-specific symbols only
     *
     * @param $string
     *
     * @return bool
     */
    public function is($string)
    {
        $regex = '
        /
          (?: [\x00-\x7F]                 # single-byte sequences   0xxxxxxx
          |   [\xC0-\xDF][\x80-\xBF]      # double-byte sequences                     110xxxxx 10xxxxxx
          |   [\xE0-\xEF][\x80-\xBF]{2}   # triple-byte sequences                     1110xxxx 10xxxxxx * 2
          |   [\xF0-\xF7][\x80-\xBF]{3}   # quadruple-byte sequence                   11110xxx 10xxxxxx * 3
        ){1,100}                        # ...one or more times
        /x';

        return (preg_match($regex, $string) == 0);
    }

    /**
     * Check whether string contain charset-specific symbols only and throw an exception
     *
     * @param $string
     *
     * @return bool
     * @throws CharsetException When string contains characters that doesn't exists in current charset
     */
    public function check($string)
    {
        if (!$this->is($string)) {
            throw new CharsetException('String contains invalid characters');
        }

        return true;
    }

    /**
     * Remove characters from string that are not in charset
     *
     * @param $string
     *
     * @return mixed
     */
    public function normalize($string)
    {
        // Remove non-utf8 characters from string http://stackoverflow.com/a/1401716/1461984
        $regex = '
        /
          (
            (?: [\x00-\x7F]                 # single-byte sequences   0xxxxxxx
            |   [\xC0-\xDF][\x80-\xBF]      # double-byte sequences                     110xxxxx 10xxxxxx
            |   [\xE0-\xEF][\x80-\xBF]{2}   # triple-byte sequences                     1110xxxx 10xxxxxx * 2
            |   [\xF0-\xF7][\x80-\xBF]{3}   # quadruple-byte sequence                   11110xxx 10xxxxxx * 3
            ){1,100}                        # ...one or more times
          )
          | .                               # anything else
        /x';

        $string = trim(preg_replace($regex, '$1', $string));

        return $string;
    }

    /**
     * Measure string length as it takes in SMS message
     *
     * @param $string
     *
     * @return int
     * @throws CharsetException When string has invalid characters
     */
    public function length($string)
    {
        $this->check($string);

        return mb_strlen($string, 'UTF-8');
    }

    /**
     * Limit string length to given characters number. If padding string given then string will be limited to length - padding length and then padded.
     *
     * @param string $string String to limit
     * @param int    $length Length limit
     * @param string $pad    String to replace cut-off part. Default is empty string. Use '...' for GSM or ASCII strings and "…" for unicode
     *
     * @return string
     */
    public function limit($string, $length, $pad = null)
    {
        $string = trim($string);

        if ($this->length($string) > $length) {
            if ($pad === null) {
                $pad = $this->options()->get('str-pad', $pad);
            }

            if ($this->length($pad) >= $length) {
                $pad = '';
            }

            $string = mb_substr($string, 0, $length - $this->length($pad), 'UTF-8');

            $string = trim($string);
            $string .= $pad;
        }

        return $string;
    }

    /**
     * Remove multiple whitespace characters, trailing spaces and other characters that doesn't affect readability
     *
     * @param $string
     *
     * @return string
     */
    public function compact($string)
    {
        $this->check($string);

        $string = preg_replace('/\s+/', ' ', $string);
        $string = trim($string);

        return $string;
    }

    /**
     * Get charset options
     *
     * @return Options
     */
    public function options()
    {
        if (!$this->options) {
            $this->options = new Options();
        }

        return $this->options;
    }
}
