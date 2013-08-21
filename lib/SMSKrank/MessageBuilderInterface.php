<?php

namespace SMSKrank;

interface MessageBuilderInterface
{
    public function build($pattern, $arguments);
}