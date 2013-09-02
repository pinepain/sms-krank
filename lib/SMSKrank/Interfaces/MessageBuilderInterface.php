<?php

namespace SMSKrank\Interfaces;

interface MessageBuilderInterface
{
    public function build($pattern, array $arguments);
}