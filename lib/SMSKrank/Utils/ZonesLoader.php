<?php

namespace SMSKrank\Utils;

use Symfony\Component\Yaml\Yaml;

use SMSKrank\Utils\Exceptions\ZonesLoaderException;

class ZonesLoader
{
    public $zones = array();
    private $source;

    public function __construct($source=null)
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

        $zone_index = $this->source . DIRECTORY_SEPARATOR . $zone . DIRECTORY_SEPARATOR . '!index.yaml';

        if (!file_exists($zone_index)) {
            throw new ZonesLoaderException("Zone file {$zone} does not exists");
        }

        if (!is_readable($zone_index)) {
            throw new ZonesLoaderException("Zone file {$zone} is not readable");
        }

        $zone_data = Yaml::parse(file_get_contents($zone_index));

        $expanded = $this->expandZoneData($zone_data);

        unset($this->zones[$zone]); // cleanup old zone data, if any

        return $this->zones += $expanded;
    }

    private function expandZoneData(array $data)
    {
        $out = array();

        foreach ($data as $code => $country) {
            $code = (string)$code;

            if (is_array($country)) {
                $country = $this->expandZoneData($country);
            } elseif ($country == '--') {
                $country = false;
//                continue; // just ignore empty ranges
            }

            if (strpos($code, '-')) {
                $_code = str_replace(' ', '', $code);
                $_code = explode('-', $_code);

                if (sizeof($_code) != 2) {
                    throw new ZonesLoaderException("Invalid range in {$code}");
                }

                list ($start, $end) = $_code;

                $size = $end - $start;

                if ($size < 1) {
                    throw new ZonesLoaderException("Invalid range in {$code} (start is equal or greater than end)");
                }

                if ($size > 10) {
                    throw new ZonesLoaderException("Invalid range in {$code} (range is too large)");
                }

                $expanded = array_fill($start, $size, $country);
                $out      = $this->joinSubZones($out, $expanded);
            } else {
                if (strlen($code) > 1) {
                    $sub = array($code[0] => $this->expandZoneData(array(substr($code, 1) => $country)));
                } else {
                    $sub = array($code => $country);
                }
                $out = $this->joinSubZones($out, $sub);
            }
        }

        return $out;
    }

    private function joinSubZones(array $existent, array $new)
    {

        foreach ($new as $sub => $nested) {

            if (isset($existent[$sub])) {

                if (!is_array($existent[$sub])) {
                    if (is_array($nested)) {
                        $placeholder = array_fill(0, 10, $existent[$sub]);
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
                        if (false === $nested) {
                            unset($existent[$sub]);
                        } else {
                            $existent[$sub] = $nested;
                        }
                    }
                } else {
                    if (is_array($nested)) {
                        $existent[$sub] = $this->joinSubZones($existent[$sub], $nested);
                    } else {
                        $existent[$sub] = $this->joinSubZones($existent[$sub], array_fill(0, 10, $nested));
                    }
                }
            } else {
                $existent[$sub] = $nested;
            }

            if (empty($existent[$sub])) {
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