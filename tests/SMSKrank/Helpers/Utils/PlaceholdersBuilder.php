<?php

namespace SMSKrank\Helpers\Utils;

use SMSKrank\MessageBuilderInterface;

class PlaceholdersBuilder implements MessageBuilderInterface
{
    public function build($pattern, $arguments)
    {
        foreach ((array)$arguments as $key => $value) {
            $pattern = str_replace('{' . $key . '}', $value, $pattern);
        }

        return $pattern;
    }
}