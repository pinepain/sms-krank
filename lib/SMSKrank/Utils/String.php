<?php

namespace SMSKrank\Utils;

class String
{
    /**
     * @param        $string
     * @param        $length
     * @param string $pad String to replace cut-off part. Default is '...', but you can replace with "…" to save 2
     *                    characters (in unicode), if phones support unicode (i guess most phone made in last 10 years does, even low-class)
     *
     * @return string
     */
    public static function limit($string, $length, $pad = "...")
    {
        if (strlen($string) <= $length) {
            return $string;
        }

        $string = substr($string, 0, $length - strlen($pad));

        // Remove non-utf8 characters from string http://stackoverflow.com/a/1401716/1461984
        $regex = '
        /
          (
            (?: [\x00-\x7F]                 # single-byte sequences   0xxxxxxx
            |   [\xC0-\xDF][\x80-\xBF]      # double-byte sequences   110xxxxx 10xxxxxx
            |   [\xE0-\xEF][\x80-\xBF]{2}   # triple-byte sequences   1110xxxx 10xxxxxx * 2
            |   [\xF0-\xF7][\x80-\xBF]{3}   # quadruple-byte sequence 11110xxx 10xxxxxx * 3
            ){1,100}                        # ...one or more times
          )

          | .                               # anything else
        /x';

        $string = preg_replace($regex, '$1', $string);

        $string = trim($string) . $pad;

        return $string;
    }

    public static function isASCII($string)
    {
        return (preg_match('/[^\x20-\x7f]/', $string) == 0);
    }
}
