<?php
/**
 * @author pba <bogdan.padalko@gmail.com>
 */

namespace SMSKrank\Gateways;

use SMSKrank\Exceptions\GatewayException;

use SMSKrank\GatewayInterface;
use SMSKrank\Message;
use SMSKrank\PhoneNumber;

class AtomparkCom implements GatewayInterface
{
    private $version = '3.0';
    private $gate = 'http://atompark.com/api/sms';

    private $public_key;
    private $private_key;
    private $sender;

    public function __construct($public_key, $private_key, $sender = null)
    {
        $this->public_key  = $public_key;
        $this->private_key = $private_key;
        $this->sender      = $sender;
    }

//    public function getSenderStatus($name, $country = null)
//    {
//        if (is_numeric($name)) {
//            // id as name
//            $args = array('idName' => $name);
//        } else {
//            // name and country
//            $args = array('name' => $name, 'country' => $country);
//        }
//
//        $json = $this->call('getSenderStatus', $args);
//
//        if (!isset($json['status'])) {
//            throw new GatewayException('Bad response body format');
//        }
//
//        return $this->decodeSenderStatus($json['status']);
//
//    }
//
//    public function registerSender($name, $country)
//    {
//        if (is_array($country)) {
//            $country = implode(',', $country);
//        }
//
//        $args = array(
//            'name'    => $name,
//            'country' => $country,
//        );
//
//        $json = $this->call('registerSender', $args);
//
//        if (!isset($json['status'])) {
//            throw new GatewayException('Bad response body format');
//        }
//
//        return $this->decodeSenderStatus($json['status']);
//    }
//
//    private function decodeSenderStatus($status)
//    {
//        // 0 - moderation, 1 - success, 2 - rejected
//        if ($status) {
//            if ($status == 1) {
//                return true; // accepted
//            }
//            return false; // rejected
//        }
//
//        return null; // moderation
//    }

    public function send(PhoneNumber $number, Message $message, \DateTime $schedule = null)
    {
        $args = array(
            'key'    => $this->public_key,
            'text'   => $message->getText(),
            'phone'  => $number->getNumber(),
            'sender' => $this->sender,
        );

        if ($schedule) {
            // NOTE: this gate traits date timezone as your location provided in cabinet
            $args['dateTimeSend'] = $schedule->format("Y-m-d H:i:s");
        }

        $json = $this->call('sendSMS', $args);

        if (!isset($json['price'])
        ) {
            throw new GatewayException('Bad response');
        }

        return (float)$json['price']; // price amount is set in user prefs
    }

    //Available currencies are USD, GBP, UAH, RUB, EUR

    /**
     * @param string $currency Available currencies are USD, GBP, UAH, RUB, EUR, if null then currency from profile
     *                         will be used. If currency invalid or not supported, USD will be used
     *
     * @return float
     * @throws \SMSKrank\Exceptions\GatewayException
     */
    public function getBalance($currency = null) // todo: test null currency
    {

        $res = $this->call('getUserBalance', array('currency' => $currency));

        if (!isset($res['balance_currency'])) {
            throw new GatewayException('Bad response body format');
        }

        return (float)$res['balance_currency'];

    }

    private function call($action, array $args = array())
    {
        $args['key']     = $this->public_key;
        $args['version'] = $this->version;
        $args['action']  = $action;

        ksort($args);
        $sum = '';

        foreach ($args as $k => $v) {
            $sum .= $v;
        }

        $sum .= $this->private_key;

        unset($args['version'], $args['action']);

        $args['sum'] = md5($sum);

        $url = "{$this->gate}/{$this->version}/{$action}?" . http_build_query($args);
//        var_dump($url);
        $res  = file_get_contents($url);
        $json = json_decode($res, true);

        if (!is_array($json) || (!isset($json['error']) && !isset($json['result']))) {
            throw new GatewayException('Bad response format');
        }

        if (isset($json['error'])) {
            // TODO: process errors here
            var_dump($json);
            throw new GatewayException('Error response: ' . $json['error']);
        } elseif (!is_array($json['result'])) {
            throw new GatewayException('Bad response body');
        }

        return $json['result'];
    }
}