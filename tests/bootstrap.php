<?php

$loader = require __DIR__ . "/../vendor/autoload.php";

$loader->add('SMSKrank\Helpers', __DIR__);
$loader->add('SMSKrank\Tests', __DIR__);

if (!function_exists('unicode_str_split')) {
    # break-up a multibyte string into its individual characters http://www.php.net/manual/en/function.mb-split.php#80046
    function unicode_str_split($string, $chunk_size = 1)
    {
        $out = array();

        $strlen = mb_strlen($string, "UTF-8");

        while ($strlen) {
            $out[]  = mb_substr($string, 0, $chunk_size, "UTF-8");
            $string = mb_substr($string, $chunk_size, $strlen, "UTF-8");
            $strlen = mb_strlen($string, "UTF-8");
        }

        return $out;
    }
}