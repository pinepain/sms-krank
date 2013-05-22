<?php

namespace SMSKrank;

use SMSKrank\Exceptions\DirectoryException;
use SMSKrank\Utils\ZonesLoader;

class Directory {
    private $zones_loader;

    public function __construct(ZonesLoader $zones_loader) {
        $this->zones_loader = $zones_loader;
    }

    public function getPhoneNumberInfo(PhoneNumber $number) {
        $zone = $this->zones_loader->get($number->getZone());

        list($calling_code, $country) = $this->getCountryCodeFromNumber(substr($number->getNumber(), 1), $zone, $number->getZone());

        if ($calling_code === false ) {
            throw new DirectoryException('Phone number calling code is not supported');
        }

        return new PhoneNumberInfo($calling_code, $country);
    }

    private function getCountryCodeFromNumber($number, array $prefixes, $code) {
        if (!empty($prefixes) && !empty($number)) {
            $n = $number[0];
            $code .= $n;

            if (isset($prefixes[$n])) {
                if (is_array($prefixes[$n])) {
                    return $this->getCountryCodeFromNumber(substr($number, 1), $prefixes[$n], $code);
                }
                return array($code, $prefixes[$n]);
            }
        }
        return array(false, false);
    }
}