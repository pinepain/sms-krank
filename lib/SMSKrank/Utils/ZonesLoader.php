<?php

namespace SMSKrank\Utils;

use Symfony\Component\Yaml\Yaml;

use SMSKrank\Utils\Exceptions\ZonesLoaderException;

class ZonesLoader
{
    public $zones = array();
    private $source;

    public function __construct($source = null)
    {
        if (!$source) {
            $source = implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', '..', '..', 'data', 'zones'));
        }

        if (!file_exists($source)) {
            throw new ZonesLoaderException('Source directory does not exists');
        }

        if (is_file($source)) {
            throw new ZonesLoaderException('Source directory is file');
        }

        $this->source = $source;
    }

    public function load($zone = null)
    {
        if (null === $zone) {
            $dir_content = array_filter(
                scandir($this->source),
                function ($val) {
                    return is_numeric($val) && $val > 1 && $val < 10;
                }
            );

            foreach ($dir_content as $zone) {
                $this->load($zone);
            }
            return $this->zones;
        }

        $loaded = $this->loadZoneDescription($zone, '!index');

        unset($this->zones[$zone]); // cleanup old zone data, if any

        return $this->zones += $loaded;
    }


    private function loadZoneDescription($zone, $desc_name)
    {
        $desc_file = $this->source . DIRECTORY_SEPARATOR . $zone . DIRECTORY_SEPARATOR . $desc_name . '.yaml';

        if (!file_exists($desc_file)) {
            throw new ZonesLoaderException("Zone #{$zone} file '{$desc_name}.yaml' does not exists");
        }

        if (!is_readable($desc_file)) {
            throw new ZonesLoaderException("Zone #{$zone} file '{$desc_name}.yaml' is not readable");
        }

        $desc_data = Yaml::parse(file_get_contents($desc_file));

        return $this->expandZoneData($desc_data);
    }


    private function expandZoneData(array $data, $prefix = '')
    {
        $out       = array();
        $out_props = array();

        // get out all props from current level

        foreach ($data as $code => $value) {
            $_code = str_replace(array('-', ' '), '', $code);

            if (!is_numeric($_code)) {
                $out_props[$code] = $value;
                unset($data[$code]);
            }
        }

        foreach ($data as $code => $country) {
            $code = (string)$code;

            // process numbers and ranges

            if (is_array($country)) {
                $country = $this->expandZoneData($country, $prefix . $code);
            } elseif ($country === '--') {
                $country = false; // not supported
            } elseif ($country === '++') {
                // support sub-zone files
                $_prefix = $prefix . $code;
                $country = $this->loadZoneDescription($_prefix[0], $_prefix);
            } elseif ($country === '==') {
                $country = array();
            } elseif ($country) {
                // support short notation <dialing or area code or any other range> : <country alpha-2 code>
                $country = array(
                    '~' => array('geo' => array('country_alpha2' => $country))
                );
            } else {
                $country = false; // no country value was provided
            }

            if (strpos($code, '-')) {
                // codes range
                $_code = str_replace(' ', '', $code);
                $_code = explode('-', $_code);

                if (sizeof($_code) != 2) {
                    throw new ZonesLoaderException("Invalid range in {$code}");
                }

                list ($start, $end) = $_code;

                $size = $end - $start + 1; // +1 because indexes are from 0 to 9 and we count from 1 to 9

                // TODO: start and end should be in [0, 9], start < end, when range is [0,9] do nothing, just set props to current level

                if ($start > 9 || $end > 9) {
                    throw new ZonesLoaderException("Invalid range in {$code} (start and end should be less then 10)");
                }

                if ($size < 0) {
                    throw new ZonesLoaderException("Invalid range in {$code} (start is equal or greater than end)");
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

    public function get($zone)
    {
        if (!isset($this->zones[$zone])) {
            $this->load($zone);
        }

        return $this->zones[$zone];
    }
}