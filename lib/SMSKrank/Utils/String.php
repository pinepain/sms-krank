<?php

namespace SMSKrank\Utils;

class String
{
    /**
     * @param        $string
     * @param        $length
     * @param string $pad String to replace cut-off part. Default is empty string ''. Use '...' for GSM strings and "…" for unicode
     *
     * @return string
     */
    public static function limit($string, $length, $pad = "")
    {
        $string  = trim($string);
        $escaped = false;

        if (self::isGSM($string)) {
            $escaped        = true;
            $working_string = self::escapeGSM($string);
        } else {
            $working_string = $string;
        }

        if (mb_strlen($working_string, 'UTF-8') <= $length) {
            return $string;
        }

        $working_string = mb_substr($working_string, 0, $length - mb_strlen($pad, 'UTF-8'), 'UTF-8');
        $working_string = trim($working_string);

        if ($escaped) {
            $working_string = self::unescapeGSM($working_string);
        }

        $working_string .= $pad;

        return $working_string;
    }

    public static function escapeGSM($string)
    {
        // In an SMS these are prefixed with the escape character (1B) and therefore take up 2 of the 160 characters of
        // an SMS. They do not need escaping when sending (most time) but we have to count them as 2 characters, rather
        // then 1, so prepend non-gsm character (☮ PEACE SYMBOL) to every character, later we just remove it.

        $string = preg_replace('/([\f^{}\\\\\[~\]\|€])/u', '☮${1}', $string);

        return $string;
    }

    public static function unescapeGSM($string)
    {
        return str_replace('☮', '', $string);
    }

    public static function cleanup($string)
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

    public static function isGSM($string)
    {
        // https://gist.github.com/michaelsanford/4978797

        /***
         * Regex string for testing GSM 7 03.38 characters in a very
         * compact way without the need for escaping special characters
         * by performing unicode range comparison. Characters mostly ordered
         * as per this table http://en.wikipedia.org/wiki/GSM_03.38
         *
         * TODO Not entirely sure what to do with SS1 Single Shift Escape
         */

        $non_gsm_regex = '/[^\x{20}-\x{7E}£¥èéùìòÇ\rØø\nÅå∆_ΦΓΛΩΠΨΣΘΞ\x{1B}ÆæßÉ ¤¡ÄÖÑÜ§¿äöñüà\x{0C}€]/u';

        return (preg_match($non_gsm_regex, $string) === 0);
//        return (mb_detect_encoding($string, 'ASCII', true) !== false);
//        return (preg_match('/[^\x20-\x7f]/', $string) == 0);
    }
}
