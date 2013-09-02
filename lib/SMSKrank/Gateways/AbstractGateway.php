<?php

namespace SMSKrank\Gateways;

use SMSKrank\GatewayInterface;
use SMSKrank\Interfaces\MessageBuilderInterface;
use SMSKrank\Message;
use SMSKrank\Utils\Options;
use SMSKrank\Utils\Packer;

abstract class AbstractGateway implements GatewayInterface
{
    protected $packer;
    protected $builder;
    protected $options;

    /**
     * Get current packer
     *
     * @return Packer | null Current packer
     */
    public function packer()
    {
        return $this->packer;
    }

    /**
     * Set packer
     *
     * @param Packer | null $packer
     *
     * @return Packer | null Old packer
     */
    public function setPacker(Packer $packer = null)
    {
        $old = $this->packer;

        $this->packer = $packer;

        return $old;
    }

    /**
     * Get messages builder
     *
     * @return MessageBuilderInterface | null Current messages builder
     */
    public function builder()
    {
        return $this->builder;
    }

    /**
     * Set messages builder
     *
     * @param MessageBuilderInterface $builder
     *
     * @return MessageBuilderInterface | null Builder packer
     */
    public function setBuilder(MessageBuilderInterface $builder = null)
    {
        $old = $this->builder;

        $this->builder = $builder;

        return $old;
    }

    /**
     * Get gateway options
     *
     * @return Options
     */
    public function options()
    {
        if (!$this->options) {
            $this->options = new Options();
        }

        return $this->options;
    }

    public function getMessageText(Message $message)
    {
        if ($this->builder()) {
            $text = $this->builder()->build($message->pattern(), $message->arguments());
        } else {
            // {?phone}:{phone} {phone?}
            // maybe using basic replace by pattern technique fits here? like "some {placholder}" + ['placeholder' => 'thing'] become "some thing"
            $text = $message->pattern();
        }

        if ($this->packer() && $this->options('msg-pack', true) && $message->options()->get('msg-pack', true)) {

            $this->packer()->pack(
                $text,
                $this->options()->get('gate-charsets', array('gsm', 'unicode')),
                $this->options()->all() + $message->options()->all()
            );
        }

        return $text;
    }

}