<?php

namespace SMSKrank\Utils;

use SMSKrank\Utils\Charsets\CharsetInterface;
use SMSKrank\Utils\Exceptions\PackerException;

class Packer
{
    private $charsets;

    public function __construct(array $charsets)
    {
        $this->charsets = new Options($charsets);
    }

    public function pack($string, array $rules, array $options)
    {
        $out     = null;
        $charset = null;

        foreach ($rules as $charset) {
            if (!$this->charsets->has($charset)) {
                throw new PackerException("Unknown character set '{$charset}'");
            }

            /** @var CharsetInterface $charset */
            $charset = $this->charsets->get($charset);

            if ($charset->is($string)) {
                $out = $string;
            } else {
                $out = $charset->normalize($string);

                if (!$charset->is($out)) {
                    $out = null;
                }
            }
        }

        if (!$out) {
            throw new PackerException("Unable to pick right charset to pack string");
        }

        $options = new Options($charset->options()->all() + $options);
        $options->set('msg-size', 1, false);

        if ($options->get('msg-compact', true)) {
            $out = $charset->compact($string);
        }

        if ($options->get('msg-size') == 1) {
            $out = $charset->limit($out, $options->get('len-single'), $options->get('str-pad'));
        } elseif ($options->get('msg-size') > 1) {
            $out = $charset->limit($out, $options->get('len-chunk'), $options->get('str-pad'));
        }

        return $out;
    }
}