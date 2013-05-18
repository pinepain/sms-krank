<?php

namespace SMSKrank;

use SMSKrank\Exceptions\DirectoryException;

class Directory {
    private $prefixes;

    public function __construct(array $prefixes) {
        $this->prefixes = $prefixes;
    }

    public function getPhoneNumberCountry(PhoneNumber $number) {
        return $this->getCountryCodeFromNumber($number->getNumber(), $this->prefixes);
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
            // TODO: should we ???
//            elseif (isset($prefixes['default'])) {
//                return $prefixes['default'];
//            }
        }

        throw new DirectoryException('Phone number calling code is not supported');
    }

}