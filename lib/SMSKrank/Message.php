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

        // $out = mb_convert_encoding($out, 'UTF-8'); // force convert to unicode

        // TODO: handle  GSM character set + Extended GSM character set
        // for more look at http://www.clockworksms.com/blog/the-gsm-character-set/ and https://gist.github.com/michaelsanford/4978797
        // NOTE: According to the standard, the alphabet for GSM 8 bit data encoding encoding is user specific, so at
        // this time we don't mess with it. As a notice, one message length is 140 chars, chained message length is 134
        if ($max_chunks > 0) {
            if ($max_chunks > 1) {
                $gsm_length     = 153 * $max_chunks;
                $unicode_length = 67 * $max_chunks;
            } else {
                $gsm_length     = 160;
                $unicode_length = 70;
            }

            $out = String::cleanup($out);

            if (String::isGSM($out)) {
                $out = String::limit(
                    $out,
                    $gsm_length,
                    $this->options()->get('chunks-pad-gsm', $this->options()->get('chunks-pad', '...'))
                );
            } else {
                $out = String::limit(
                    $out,
                    $unicode_length,
                    $this->options()->get('chunks-pad-unicode', $this->options()->get('chunks-pad', '…'))
                );
            }
        }

        return $out;
    }

    public function options()
    {
        return $this->options;
    }
}