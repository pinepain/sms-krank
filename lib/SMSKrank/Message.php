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

        // limit to one message
        $max_len = $this->options->get('max-length', 160);

        if ($max_len > 0 && strlen($out) > $max_len) {
            $out = String::limit($out, $max_len, $this->options()->get('max-length-pad', '...'));
        }

        return $out;
    }

    public function options()
    {
        return $this->options;
    }
}