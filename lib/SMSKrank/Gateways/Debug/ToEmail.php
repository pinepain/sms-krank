<?php

namespace SMSKrank\Gateways\Debug;

use PHPMailer;
use SMSKrank\Exceptions\GatewayException;
use SMSKrank\Interfaces\SenderInterface;
use SMSKrank\Message;
use SMSKrank\PhoneNumber;

class ToEmail implements SenderInterface
{
    private $from;
    private $to;
    private $host;
    private $port;
    private $user;
    private $password;

    public function __construct($from, $to = null, $host = 'localhost', $port = 25, $user = null, $password = null)
    {
        $this->from     = $from;
        $this->to       = is_array($to) ? $to : array($to);
        $this->host     = $host;
        $this->port     = $port;
        $this->user     = $user;
        $this->password = $password;
    }

    public function addRecipient($recipient)
    {
        if (is_array($recipient)) {
            foreach ($recipient as $r) {
                $this->addRecipient($r);
            }

        } else {
            if (!in_array($recipient, $this->to)) {
                $this->to[] = $recipient;
            }
        }

        return true;
    }

    public function delRecipient($recipient)
    {
        if (is_array($recipient)) {
            foreach ($recipient as $r) {
                $this->delRecipient($r);
            }

        } else {
            if (($key = array_search($recipient, $this->to)) !== false) {
                unset($this->to[$key]);
            }
        }

        return true;
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
        $mailer = new PHPMailer();
        $mailer->IsSMTP();
        $mailer->Port = $this->port;
        $mailer->Host = $this->host;

        if ($this->user || $this->password) {
            $mailer->SMTPAuth = true;
            $mailer->Username = $this->user;
            $mailer->Password = $this->password;
        }

        $mailer->CharSet = "UTF-8";
        $mailer->From    = $this->from;
        $mailer->Subject = "SMS-Krank Debug Gateway";

        if (empty($this->to)) {
            throw new GatewayException('No recipients');
        }

        foreach ($this->to as $to) {
            if (!$mailer->AddAddress($to)) {
                throw new GatewayException("Invalid recipient: '{$to}'");
            }
        }
        $nl = "\r\n";

        $mailer->Body .= "Phone: " . $number->number() . $nl;
        $mailer->Body .= "Message: " . $message->text() . $nl;
        $mailer->Body .= "Scheduled: " . ($schedule ? $schedule->format(\DateTime::ISO8601) : 'immediately');

        if (!$mailer->Send()) {
            throw new GatewayException("Failed to send");
        }

        return null;
    }
}