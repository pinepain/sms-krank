<?php

namespace SMSKrank;

use SMSKrank\Utils\String;
use SMSKrank\Utils\Options;

class Message
{
    private $pattern;
    private $arguments;
    private $builder;

    private $options;

    public function __construct($pattern, $arguments = null, MessageBuilderInterface $builder = null)
    {
        $this->pattern   = $pattern;
        $this->arguments = $arguments;
        $this->builder   = $builder;

        $this->options = new Options();
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function getText()
    {
        if ($this->builder) {
            $out = $this->builder->build($this->getPattern(), $this->getArguments());
        } else {
            $out = $this->getPattern();
        }

        if ($this->options->get('compact', true)) {
            $out = preg_replace('/\s+/', ' ', $out);
            $out = trim($out);
        }

        // NOTE: 16bit encoded message max size is 70, but we use unicode, right?
        // limit to one message
        $max_chunks = $this->options->get('chunks', 1);

        $ascii_length   = 160;
        $unicode_length = 140;

        // $out = mb_convert_encoding($out, 'UTF-8'); // force convert to unicode

        if ($max_chunks > 0) {
            if (String::isASCII($out)) {
                $max_length = $ascii_length;
            } else {
                $max_length = $unicode_length;
            }

            $max_length *= $max_chunks;

            if (strlen($out) > $max_length) {
                $out = String::limit($out, $max_length, $this->options()->get('chunks-pad', '...'));
            }
        }

        return $out;
    }

    public function options()
    {
        return $this->options;
    }
}