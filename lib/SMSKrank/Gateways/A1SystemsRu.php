<?php
/**
 * @url http://a1systems.ru//
 */

namespace SMSKrank\Gateways;

use SMSKrank\Exceptions\GatewayException;
use SMSKrank\GatewayInterface;
use SMSKrank\Message;
use SMSKrank\PhoneNumber;

class A1SystemsRu implements GatewayInterface
{
    private $login;
    private $password;
    private $sender;

    public function __construct($login, $password, $sender)
    {
        $this->login    = $login;
        $this->password = $password;
        $this->sender   = $sender;
    }

    /**
     * @param PhoneNumber $number
     * @param Message     $message
     * @param \DateTime   $schedule
     *
     * @throws \SMSKrank\Exceptions\GatewayException
     *
     * @return float | null Message fee, if available. Null otherwise
     */
    public function send(PhoneNumber $number, Message $message, \DateTime $schedule = null)
    {

        $args = array(
            'operation' => 'send',
            'login'     => $this->login,
            'password'  => $this->password,
            'msisdn'    => $number->getNumber(),
            'shortcode' => $this->sender,
            'text'      => $message->getText(),
        );

        $url = 'http://http.a1smsmarket.ru:8000/send?' . http_build_query($args);
        $res = file_get_contents($url);

        if (strpos($res, ':')) {
            list(, $error) = explode(':', $res);
            throw new GatewayException(trim($error));
        }

        $id = (int)$res; // retrieve message id

        if ($id < 1) {
            throw new GatewayException('Bad response');
        }

        return null;
    }

}