<?php

namespace SMSKrank;

use SMSKrank\Interfaces\MessageBuilderInterface;
use SMSKrank\Utils\Options;

class Message
{
    private $pattern;
    private $arguments;

    private $options;

    public function __construct($pattern, $arguments = null, array $options = array())
    {
        $this->pattern   = $pattern;
        $this->arguments = $arguments;

        $this->options = new Options($options);
    }

    public function pattern()
    {
        return $this->pattern;
    }

    public function arguments()
    {
        return $this->arguments;
    }

    public function options()
    {
        return $this->options;
    }
}