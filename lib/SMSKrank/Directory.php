<?php

namespace SMSKrank;

use SMSKrank\Exceptions\DirectoryException;
use SMSKrank\Utils\ZonesLoader;

class Directory {
    private $zones_loader;

    public function __construct(ZonesLoader $zones_loader) {
        $this->zones_loader = $zones_loader;
    }

    public function getPhoneNumberCountry(PhoneNumber $number) {
        $zone = $this->zones_loader->get($number->getZone());

        $country = $this->getCountryCodeFromNumber(substr($number->getNumber(), 1), $zone);

        if ($country === false ) {
            throw new DirectoryException('Phone number calling code is not supported');
        }

        return $country;
    }

    private function getCountryCodeFromNumber($number, array $prefixes) {
        if (!empty($prefixes) && !empty($number)) {
            $n = $number[0];

            if (isset($prefixes[$n])) {
                if (is_array($prefixes[$n])) {
                    return $this->getCountryCodeFromNumber(substr($number, 1), $prefixes[$n]);
                }
                return $prefixes[$n];
            }
        }
        return false;
    }

}