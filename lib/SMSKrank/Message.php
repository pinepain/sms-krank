<?php

namespace SMSKrank;

class Message
{
    private $text;

    public function __construct($text)
    {
        $this->text = $text;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getLength()
    {
        return strlen($this->text);
    }
}