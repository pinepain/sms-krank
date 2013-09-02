<?php

namespace SMSKrank\Loaders\Parsers;

use SMSKrank\Loaders\LoaderInterface;

interface ParserInterface {
    public function parse(array $data, $section, LoaderInterface $loader);
}