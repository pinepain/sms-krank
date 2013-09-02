<?php

namespace SMSKrank\Gateways\Debug;

use SMSKrank\Exceptions\GatewayException;
use SMSKrank\Interfaces\SenderInterface;
use SMSKrank\Message;
use SMSKrank\PhoneNumber;

class ToFile implements SenderInterface
{
    private $file_name;
    private $format;

    public function __construct($file_name, $format = '{datetime}: {phone} - "{message}" {schedule}')
    {
        $this->file_name = $file_name;
        $this->format    = $format;

    }

    public function send(PhoneNumber $number, Message $message, \DateTime $schedule = null)
    {
        if ($this->file_name == '/dev/null') {
            // add null device support for non-*NIX users
            return null;
        }

        $template = $this->format;
        $schedule = $schedule ? 'at ' . $schedule->format(\DateTime::ISO8601) : 'immediately';

        $now      = new \DateTime();
        $template = str_replace('{datetime}', $now->format(\DateTime::ISO8601), $template);
        $template = str_replace('{phone}', $number->number(), $template);
        $template = str_replace('{schedule}', $schedule, $template);

        // message may contain any data, even template placeholders, so process it aat last
        $template = str_replace('{message}', $message->text(), $template);

        $fh = fopen($this->file_name, 'w+');
        if (!$fh) {
            throw new GatewayException("Failed to open file '{$this->file_name}'");
        }

        fwrite($fh, $template . PHP_EOL);
        fclose($fh);

        return null;
    }
}