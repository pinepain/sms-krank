<?php

namespace SMSKrank;

use SMSKrank\Exceptions\ExchangeException;
use SMSKrank\Exceptions\GatewayException;
use SMSKrank\Exceptions\PhoneNumberDetailedException;
use SMSKrank\Utils\AbstractLoader;
use SMSKrank\Utils\Exceptions\LoaderException;

class Exchange implements GatewayInterface
{
    private $maps_loader;
    private $gateway_factory;
    private $directory;

    public function __construct(AbstractLoader $maps_loader, GatewayFactory $gateway_factory, Directory $directory)
    {
        $this->maps_loader     = $maps_loader;
        $this->gateway_factory = $gateway_factory;
        $this->directory       = $directory;
    }

    public function send(PhoneNumber $number, Message $message, \DateTime $schedule = null)
    {
        if ($number instanceof PhoneNumberDetailed) {
            $detailed_phone_number = $number;
        } else {
            $detailed_phone_number = $this->directory->getPhoneNumberDetailed($number->getNumber());
        }

        try {
            $gateways = $this->maps_loader->get($detailed_phone_number->getGeo('country_alpha2'));
        } catch (LoaderException $e) {
            throw new ExchangeException("Failed to send message (unable to load country)");
        } catch (PhoneNumberDetailedException $e) {
            throw new ExchangeException("Failed to send message (phone number is not supported)");
        }

        $price = false;

        foreach ($gateways as $gate_name => $required_props) {

            if (null != $required_props) {
                // required props are array with at least one property
                // all properties should exist in phone number and have the same value

                $phone_props = $detailed_phone_number->getProp() + array(
                        'geo'  => $detailed_phone_number->getGeo(),
                        'code' => $detailed_phone_number->getCode()
                    );

                // TODO: support OR, NOT for nested props, for now I don't need this and it is a bit complicated piece of code
                // NOTE: we use AND logic for comparison, so to use gate phone should have all required props with the same values

                $intersection = $this->array_intersect_assoc_recursive($required_props, $phone_props);

//                var_dump($required_props);
//                var_dump($detailed_phone_number);
//                var_dump($intersection);
//                echo '-----------------------------', PHP_EOL;

                if ($required_props !== $intersection) {
                    continue; // no match, try the next one
                }
            }

            try {
                $gate  = $this->gateway_factory->getGateway($gate_name);
                $price = $gate->send($detailed_phone_number, $message, $schedule);
                break;
            } catch (LoaderException $e) {
            } catch (GatewayException $e) {
            }
        }

        if (false === $price) { // when price for sent message is not available it should be set to null
            throw new ExchangeException("Failed to send message (unable to pick working gate)");
        }

        return $price;
    }

    /**
     * array_intersect_assoc() recursively
     *
     * @param $arr1
     * @param $arr2
     *
     * @see http://stackoverflow.com/questions/4627076/php-question-how-to-array-intersect-assoc-recursively
     *
     * @return array|bool
     */
    private function array_intersect_assoc_recursive($arr1, $arr2)
    {
        if (!is_array($arr1) || !is_array($arr2)) {
            // return $arr1 == $arr2; // Original line, raise warnings when compare array to string
            return (string)$arr1 == (string)$arr2;
        }

        $commonkeys = array_intersect(array_keys($arr1), array_keys($arr2));

        $ret = array();

        foreach ($commonkeys as $key) {
            $res = $this->array_intersect_assoc_recursive($arr1[$key], $arr2[$key]);
            if ($res) {
                $ret[$key] = $arr1[$key];
            }
        }

        return $ret;
    }
}