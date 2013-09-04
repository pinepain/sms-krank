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

    protected function isCodePlain($string)
    {
        return is_numeric($string);
    }

    protected function isCodeRange($string)
    {
        return (strpos($string, '~') > 0); // for example, range 2~4
    }

    protected function isCodeList($string)
    {
        return (strpos($string, ',') > 0); // for example, 2,3,5,124,432
    }

    protected function isCode($string)
    {
        return $this->isCodePlain($string) || $this->isCodeRange($string) || $this->isCodeList($string);
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

            if ($this->isCodeList($code)) {
                $out = $this->expandCodeList($code, $country);
            } elseif ($this->isCodeRange($code, $country)) {
                $out = $this->expandCodeRange($code, $country);
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

    protected function expandCodeList($code, $country)
    {
        $_code = explode(',', $code);
        $_code = array_filter($_code); // remove empty values

        $out = array();

        foreach ($_code as $c) {
            $c = trim($c);

            if ($this->isCodeRange($c)) {
                $sub = $this->expandCodeRange($c, $country);
            } elseif ($this->isCodePlain($c)) {
                $sub = $this->expandCode($c, $country);
            } else {
                throw new ZonesParserException("Invalid code list '{$code}' (bad code '{$c}')");
            }

            $out = $this->joinSubZones($out, $sub);
        }

        return $out;
    }

    protected function expandCodeRange($code, $country)
    {
        throw new ZonesParserException('Code ranges are not implemented yet');

        $_code = explode('~', $code);

        // TODO support nested zones parsing with optimization
        if (sizeof($_code) != 2) {
            throw new ZonesParserException("Invalid range '{$code}'");
        }

        list ($start, $end) = $_code;

        if (!$this->isCodePlain($start)) {
            throw new ZonesParserException("Invalid range '{$code}' start code '{$start}'");
        }

        if (!$this->isCodePlain($end)) {
            throw new ZonesParserException("Invalid range '{$code}' end code '{$end}'");
        }

        if ($end <= $start) {
            throw new ZonesParserException("Invalid range '{$code}' size, end is less or same to start");
        }

        $start = str_split($start);
        $end   = str_split($end);

        // TODO: it would be better to this with strings
        $start = array_pad($start, count($end), 0); // for situations like 12~135 which transform to 120~135

        // 200~300 may be simplified to 2~3, 234~245 may be simplified to 2 => [34~45], 234~239 -> 23 => [4-9]
        // 235~480 -> 235~299,3,400~480

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

        return $out;
    }

    protected function insertCodes($current, array $codes)
    {
        if ($current === false) {
            // nest white-listed code to previously blacklisted code

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