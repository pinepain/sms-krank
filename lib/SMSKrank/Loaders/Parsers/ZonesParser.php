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
            var_dump(func_get_args());
            die;
            $keys = implode(', ', array_keys($res));
            throw new ZonesParserException("Root zone '{$section}' possible collision (zones present: {$keys})");
        }

        return $res;
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

            if (!is_numeric($code)) {
                $out_props[$code] = $country;
                continue;
            }

            $_prefix = $prefix . $code;
            if (empty($prefix)) {
                $_prefix = substr($prefix . $code, 1);
            }

            // process numbers and ranges

            if (is_array($country)) {
                $country = $this->expandZoneData($country, $zone, $_prefix, $loader);
            } elseif ($country === '--') {
                $country = false; // not supported
            } elseif ($country === '++') {
                if (empty($_prefix)) {
                    throw new ZonesParserException("Root description for zone '{$zone}' couldn't be loaded from sub-zones directory");
                }
                // support sub-zone files
                $country = $loader->load($zone . DIRECTORY_SEPARATOR . $_prefix, true);
            } elseif ($country === '==') {
                $country = array();
            } elseif ($country) {
                // support short notation <dialing or area code or any other range> : <country alpha-2 code>
                $country = array(
                    '~' => array(
                        'geo'  => array('country_alpha2' => $country),
                        'code' => array('country' => $prefix . $code)
                    )
                );
            } else {
                $country = false; // no country value was provided
            }

            if (strpos($code, '-')) {
                // codes range
                $_code = str_replace(' ', '', $code);
                $_code = explode('-', $_code);

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

            } else {
                // numeric code

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
                $out = $this->joinSubZones($out, $sub);
            }
        }

        ksort($out);

        if (!empty($out_props)) {
            ksort($out_props);
            $out['~'] = $out_props;
        }

        return $out;
    }

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
                        $placeholder    = array_fill(0, 10, $existent[$sub]);
                        $existent[$sub] = $this->joinSubZones($placeholder, $nested);
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

            if ($existent[$sub] == false) {
                unset($existent[$sub]);
            }
        }

        return $existent;
    }
}