<?php

namespace SMSKrank\Loaders\Parsers;

use SMSKrank\Loaders\LoaderInterface;
use SMSKrank\Loaders\Parsers\Exceptions\ZonesParserException;

// TODO: load by one or more numbers (like in directory getNumberProps)
class ZonesParser implements ParserInterface
{
    public function parse(array $data, $section, LoaderInterface $loader)
    {
        // NOTE: load with one-shot all further files cause they should be stored nested, not in container root
        // TODO: Implement parse() method.

        // TODO: support $section or get rid of it
        $res = $this->expandZoneData($data, $section, '', $loader);

        if ($section > 0 && $section < 10 && count($res) != 1) {
            $keys = implode(', ', array_keys($res));
            throw new ZonesParserException("Root zone '{$section}' possible collision (zones present: {$keys})");
        }

        return $res;
    }

    public function isCode($string)
    {
        if (is_numeric($string) || count(explode('~', $string)) == 2) {
            return true;
        }

        return false;
    }

    private function expandZoneData(array $data, $zone, $prefix, LoaderInterface $loader)
    {
        $out       = array();
        $out_props = array();

        // get out all props from current level
//
//        foreach ($data as $code => $value) {
//            $_code = str_replace(array('+', '-', '/', ' ', '(', ')'), '', $code);
//
//            if (!is_numeric($_code)) {
//                $out_props[$code] = $value;
//                unset($data[$code]);
//            }
//        }

        foreach ($data as $code => $country) {
            $code = (string)$code;
            $code = str_replace(array('+', '-', '/', ' ', '(', ')'), '', $code);

            // TODO: ranges support 23-31: FOO, enum support 23,34,42: BAR
            if (!$this->isCode($code)) {
                $out_props[$code] = $country;
                continue;
            }

            // calculate prefix for sub-zones loading
            $_prefix = $prefix . $code;
            if (empty($prefix)) {
                $_prefix = substr($prefix . $code, 1); // remove zone number from prefix
            }

            if (is_array($country)) {
                // extended code description, code contain other sub-codes and properties
                $country = $this->expandZoneData($country, $zone, $_prefix, $loader);
            } elseif ($country === '--') {
                // mark code as black-listed
                $country = false;
            } elseif ($country === '++') {
                // load code description from other location (file, cache key, etc)

                if (empty($_prefix)) {
                    throw new ZonesParserException("Root description for zone '{$zone}' couldn't be loaded from sub-zones directory");
                }

                $country = $loader->load($zone . DIRECTORY_SEPARATOR . $_prefix, true);
            } elseif ($country) {
                // support short notation <dialing or area code or any other range> : <country alpha-2 code>
                $country = array(
                    '~' => array(
                        'geo'  => array('country_alpha2' => $country),
                        'code' => array('country' => $prefix . $code)
                    )
                );
            } else {
                throw new ZonesParserException("No country value provided for code '{$code}' in zone '{$zone}' (prefix '{$prefix}')");
            }

            if (strpos($code, '~')) {
                // codes range
                throw new \Exception('TODO: implement');
            } else {
                // numeric code
                $sub = $this->expandCode($code, $country);

                $out = $this->joinSubZones($out, $sub);
            }
        }

        ksort($out);

        // zone or code description parameters may be omitted when they are empty
        if (!empty($out_props)) {
            ksort($out_props);
            $out['~'] = $out_props;
        }

        return $out;
    }

    protected function expandCode($code, $country)
    {
        if (strlen($code) > 1) {
            // greater than 9, so contain more than one digit

            $pos      = strlen($code) - 1;
            $expanded = array($code[$pos] => $country);

            for ($pos--; $pos >= 0; $pos--) {
                $expanded = array($code[$pos] => $expanded);
            }

            $sub = $expanded;
        } else {
            $sub = array($code => $country);
        }

        return $sub;
    }

    protected function expandCodesRange($code, $country)
    {
        $_code = explode('~', $code);

        // TODO support nested zones parsing with optimization
        if (sizeof($_code) != 2) {
            throw new ZonesParserException("Invalid range in {$code}");
        }

        list ($start, $end) = $_code;

        $size = $end - $start + 1; // +1 because indexes are from 0 to 9 and we count from 1 to 9

        // TODO: start and end should be in [0, 9], start < end, when range is [0,9] do nothing, just set props to current level

        if ($start > 9 || $end > 9) {
            throw new ZonesParserException("Invalid range in {$code} (start and end should be less then 10)");
        }

        if ($size < 0) {
            throw new ZonesParserException("Invalid range in {$code} (start is equal or greater than end)");
        }

        if ($size == 10) {
            // optimization: do not fill whole range with same data, set props on current level and expand data on the same level
            $out = $country;
        } else {
            $expanded = array_fill($start, $size, $country);
            $out      = $this->joinSubZones($out, $expanded);
        }

    }

    protected function insertCodes($current, array $codes)
    {
        if ($current === false) {
            // nest white-listed code to previously blacklisted code
            echo "join with fill to blacklisted\n";

            $current = array_fill(0, 10, $current);

            foreach ($codes as $_c => $_v) {
                $current[$_c] = $_v;
            }

            // code blacklisted, just add nested value to it
        } else {
            $placeholder = array_fill(0, 10, $current);
            $current     = $this->joinSubZones($placeholder, $codes);
        }

        return $current;
    }

    // TODO: split this method to smaller ones, it decrease code complexity and allow us to test it properly
    private function joinSubZones(array $existent, array $new)
    {
        foreach ($new as $sub => $nested) {

            if (isset($existent[$sub])) {

                if ('~' === $sub) {

//                    foreach ($new[$sub] as $k => $v) {
//                        $existent[$sub][$k] = $v;
//                    }

                    $existent[$sub] = array_merge($existent[$sub], $new[$sub]);

                    continue;
                } elseif (!is_array($existent[$sub])) {
                    if (is_array($nested)) {
                        $existent[$sub] = $this->insertCodes($existent[$sub], $nested);
                    } else {
//                        $_nested = false === $nested ? "<empty set>" : $nested;
//                        if ($existent[$sub] == $nested) {
//                            // potential mistake: zone record occurred multiple time under different path
//                            trigger_error(
//                                "Potential records mistake: {$_nested} occurred more than one time under same calling code",
//                                E_USER_NOTICE
//                            );
//                        } else {
//                            // potential conflict: two different zone records have the same calling code
//                            trigger_error(
//                                "Potential records conflict: {$existent[$sub]} will be overridden by {$_nested}",
//                                E_USER_WARNING
//                            );
//                        }
                    }
                } else {
                    if (is_array($nested)) {
                        $existent[$sub] = $this->joinSubZones($existent[$sub], $nested);
                    } else {
                        if (false === $nested) {
//                            unset($existent[$sub]);
                            $existent[$sub] = $nested;
                        } else {
//                            $existent[$sub] = $nested;
                            $existent[$sub] = $this->joinSubZones($existent[$sub], array_fill(0, 10, $nested));
                        }

                    }
                }

            } else {
                $existent[$sub] = $nested;
            }

//            if ($existent[$sub] === false) {
//                unset($existent[$sub]);
//            }
        }

        return $existent;
    }
}