<?php

namespace SMSKrank;

use SMSKrank\Exceptions\ExchangeException;
use SMSKrank\Exceptions\GatewayException;
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
        $detailed_phone_number = $this->directory->getPhoneNumberDetailed($number->getNumber());

        $gateways = $this->maps_loader->get($detailed_phone_number->getGeo('country_alpha2'));

        $price = false;

        foreach ($gateways as $gate_name => $required_props) {

            if (null != $required_props) {
                // required props are array with at least one property
                // all properties should exist in phone number and have the same value

                // TODO: support OR, NOT for nested props, for now I don't need this and it is a bit complicated piece of code
                // NOTE: we use AND logic for comparison, so to use gate phone should have all required props with the same values
                $intersection = array_uintersect_assoc(
                    $required_props,
                    $detailed_phone_number->getProp(),
                    function ($a, $b) {
                        return $a !== $b; // 0 - equal, which is false casted to int
                    }
                );

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
            throw new ExchangeException("Failed to send message");
        }

        return $price;
    }
}