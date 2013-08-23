<?php
/**
 * @url http://sms-uslugi.ru
 */

namespace SMSKrank\Gateways;

use SMSKrank\Exceptions\GatewayException;
use SMSKrank\GatewayInterface;
use SMSKrank\Message;
use SMSKrank\PhoneNumber;

class SmsUslugiRu implements GatewayInterface
{
    private $login;
    private $password;

    public function __construct($login, $password)
    {
        $this->login    = $login;
        $this->password = $password;

    }

    public function send(PhoneNumber $number, Message $message, \DateTime $schedule = null)
    {
        $args = array(
            'login'        => $this->login,
            'password'     => $this->password,
            'txt'          => $message->getText(), // TODO: text should be UTF-8 encoded
            'to'           => $number->getNumber(),
            'onlydelivery' => 1
        );

        if ($schedule) {
            // NOTE: this gate traits date timezone as your location provided in cabinet
            $args['dateTimeSend'] = $schedule->format("Y-m-d H:i:s");
        }

        // response as JSON (no respect to HTTP status code, btw, very nice. curl is useless here)
        // https://lcab.sms-uslugi.ru/lcabApi/sendSms.php

        $res = file_get_contents("https://lcab.sms-uslugi.ru/lcabApi/sendSms.php?" . http_build_query($args));

        $json = json_decode($res, true);

        if (!is_array($json) || !isset($json['code'])) {
            throw new GatewayException('Bad response');
        }

        // TODO: return message(s) cost
        return ($json['code'] == 1);
    }

    /**
     * @return float Balance in RUB
     * @throws \SMSKrank\Exceptions\GatewayException
     */
    public function getBalance()
    {
        $args = array(
            'login'    => $this->login,
            'password' => $this->password,
        );

        $res  = file_get_contents("https://lcab.sms-uslugi.ru/lcabApi/info.php?" . http_build_query($args));
        $json = json_decode($res, true);

        if (!is_array($json) || !isset($json['account'])) {
            throw new GatewayException('Bad response');
        }

        return (float)$json['account'];
    }

}

