<?php

namespace SMSKrank\Loaders\Parsers;

use SMSKrank\Loaders\LoaderInterface;
use SMSKrank\Loaders\Parsers\Exceptions\MapsParserException;
use SMSKrank\Loaders\Parsers\ParserInterface;

class MapsParser implements ParserInterface
{
    public function parse(array $data, $section, LoaderInterface $loader)
    {
        if (empty($data)) {
            throw new MapsParserException("Map '{$section}' is empty");
        }

        foreach ($data as $gate => $filter) {
            if (!is_array($filter)) {
                if ('any' === $filter) {
                    $data[$gate] = null;
                } else {
                    throw new MapsParserException("Map '{$section}' filter for gate '{$gate}' invalid");
                }
            }

            if (!$loader->has($gate)) {
                throw new MapsParserException("Gateway '{$gate}' from '{$section}' map doesn't exists");
            }
        }

        return array($section => $data);
    }
}
