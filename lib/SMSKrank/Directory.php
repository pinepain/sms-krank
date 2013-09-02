<?php

namespace SMSKrank;

use SMSKrank\Exceptions\DirectoryException;
use SMSKrank\Loaders\Exceptions\LoaderException;
use SMSKrank\Utils\ZonesLoader;

class Directory
{
    private $mandatory = array('geo', 'code', 'validation');
    private $zones_loader;

    public function __construct(ZonesLoader $zones_loader)
    {
        $this->zones_loader = $zones_loader;
    }

    public function getPhoneNumberDetailed($number)
    {
        // TODO: add detalisation level: required props or max search depth

        $number = preg_replace('/[^0-9]/', '', $number);

        if (!strlen($number)) {
            throw new DirectoryException("Empty phone number");
        }

        try {
            $zone_desc = $this->zones_loader->get($number[0]);

        } catch (LoaderException $e) {
            throw new DirectoryException('Phone number calling code is not supported');
        }

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
        $this->validateNumber($number, $out['validation']);

        return new PhoneNumberDetailed($number, $out['code'], $out['geo'], $props);

    }

    private function validateNumber($number, array $rules)
    {
        if (isset($rules['icc']) && isset($rules['ncc']) && strlen($number) != $rules['icc'] + $rules['ncc']) {
            throw new DirectoryException("Validation failed for '{$number}' phone number (invalid length)");
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