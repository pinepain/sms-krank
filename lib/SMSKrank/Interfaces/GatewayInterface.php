<?php

namespace SMSKrank\Interfaces;

use SMSKrank\Utils\Options;
use SMSKrank\Utils\Packer;

interface GatewayInterface extends SenderInterface
{
    /**
     * Get current packer
     *
     * @return Packer | null Current packer
     */
    public function packer();

    /**
     * Set packer
     *
     * @param Packer | null $packer
     *
     * @return Packer | null Old packer
     */
    public function setPacker(Packer $packer = null);

    /**
     * Get messages builder
     *
     * @return MessageBuilderInterface | null Current messages builder
     */
    public function builder();

    /**
     * Set messages builder
     *
     * @param MessageBuilderInterface $builder
     *
     * @return MessageBuilderInterface | null Builder packer
     */
    public function setBuilder(MessageBuilderInterface $builder = null);

    /**
     * Get gateway options
     *
     * @return Options
     */
    public function options();
}