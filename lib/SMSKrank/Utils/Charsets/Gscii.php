<?php

namespace SMSKrank\Utils\Charsets;

use SMSKrank\Utils\Options;

class Gscii implements CharsetInterface
{
    protected $options;

    public function __construct(array $options = array())
    {
        $this->options()->set($options);

        $this->options()->set(
            array(
                'str-pad'    => '...',
                'len-single' => 160,
                'len-chunks' => 153,
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
        $regex = '/[^\x{0A}\x{0C}\x{0D}\x{20}-\x{5F}\x{61}-\x{7E}\x{1B}]/';

        return (preg_match($regex, $string) === 0);
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
        $regex = '
        /
          (
            (?:
              [\x{0A}\x{0C}\x{0D}\x{20}-\x{5F}\x{61}-\x{7E}\x{1B}] # characters that exists in GSM and ASCII
            ){1,100}                        # ...one or more times
          )
          | .                               # anything else
        /xu';

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

        $string = $this->escape($string);

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

            $string = $this->escape($string);

            if ($this->length($pad) >= $length) {
                $pad = '';
            }

            $string = mb_substr($string, 0, $length - $this->length($pad), 'UTF-8');

            $string = $this->unescape($string);
            $string = trim($string);

            $string .= $pad;

            return $string;
        }

        return $string;
    }

    /**
     * Remove multiple whitespace characters, trailing spaces and other characters that doesn't affect readability
     *
     * @param string $string
     *
     * @return string
     */
    public function compact($string)
    {
        $this->check($string);

        $string = preg_replace('/[\s\x{1B}]+/', ' ', $string);
        $string = trim($string);

        return $string;
    }

    protected function escape($string)
    {
        // In an SMS these are prefixed with the escape character (1B) and therefore take up 2 of the 160 characters of
        // an SMS. They do not need escaping when sending (most time) but we have to count them as 2 characters, rather
        // then 1, so prepend non-gsm character (☮ PEACE SYMBOL) to every character, later we just remove it.

        $string = preg_replace('/([\x{0C}^{}\\\\\[~\]\|])/', '☮${1}', $string);

        return $string;
    }

    protected function unescape($string)
    {
        return str_replace('☮', '', $string);
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
