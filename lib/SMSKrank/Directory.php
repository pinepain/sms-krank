<?php

namespace SMSKrank;

use SMSKrank\Exceptions\DirectoryException;
use SMSKrank\Loaders\LoaderInterface;
use SMSKrank\PhoneNumbers\Detailed;
use SMSKrank\PhoneNumbers\Parsers\PhoneNumberParserInterface;
use SMSKrank\PhoneNumbers\Plain;
use SMSKrank\Utils\Options;

class Directory
{
    protected $mandatory = array('geo', 'code', 'validation');
    protected $zones_loader;
    protected $numbers_parser;

    public function __construct(LoaderInterface $zones_loader, PhoneNumberParserInterface $numbers_parser)
    {
        $this->zones_loader   = $zones_loader;
        $this->numbers_parser = $numbers_parser;
    }

    public function getPhoneNumber($number)
    {
        $number = $this->numbers_parser->parse($number);

        $this->zones_loader->get($number[0]); // just to be sure that zone is supported

        return new Plain($number);
    }

    public function getPhoneNumberDetailed($number)
    {
        // TODO: add detalisation level: required props or max search depth

        $number = $this->numbers_parser->parse($number);

        $zone_desc = $this->zones_loader->get($number[0]);

        $props = $this->getNumberProps(substr($number, 1), $zone_desc, $number[0]);

        $out = array();

        foreach ($this->mandatory as $field) {
            if (isset($props[$field])) {
                $out[$field] = $props[$field];
                unset($props[$field]);
            } else {
                $out[$field] = array();
            }
        }

        // TODO: validate only if phone parsed in full depth
        $this->validateNumber($number, new Options($out['validation']));

        return new Detailed($number, $out['code'], $out['geo'], $props);
    }

    private function validateNumber($number, Options $rules)
    {
        $len = strlen($number);

        if ($rules->has('length') && $len != $rules->get('lenght')) {
            throw new DirectoryException("Phone number '{$number}' has invalid fixed length");
        }

        if ($rules->has('min-length') && $len < $rules->get('length')) {
            throw new DirectoryException("Phone number '{$number}' has invalid minimal length");
        }

        if ($rules->has('max-length') && $len > $rules->get('length')) {
            throw new DirectoryException("Phone number '{$number}' has invalid max length");
        }
    }

    private function getNumberProps($number, array $desc, $lead, array $props = array())
    {
        if (!empty($desc) && !empty($number)) {
            $n = $number[0];
            $lead .= $n;

            if (isset($desc['~'])) {
                // load props
                $props = array_replace_recursive($props, $desc['~']);
            }

            if (isset($desc[$n])) {
                return $this->getNumberProps(substr($number, 1), $desc[$n], $lead, $props);
            }
        }

        return $props;
    }
}