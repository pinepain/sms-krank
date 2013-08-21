<?php


namespace SMSKrank\Tests;

use SMSKrank\Utils\GatewaysLoader;
use SMSKrank\GatewayFactory;

class GatewayFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GatewayFactory
     */
    private $gateway;

    protected function setUp()
    {
        $gateway_loader = new GatewaysLoader(__DIR__ . '/../../data/gates/gates-valid'); // taken from GatewayLoaderTest

        $this->gateway = new GatewayFactory($gateway_loader);
    }

    /**
     * @covers  SMSKrank\GatewayFactory::getGateway
     */
    public function testGetCached()
    {
        $gate_1 = $this->gateway->getGateway('first');
        $gate_2 = $this->gateway->getGateway('first');

        $this->assertSame($gate_1, $gate_2);
    }

    /**
     * @covers SMSKrank\GatewayFactory::getGateway
     */
    public function testGetOneShot()
    {
        $gate_1 = $this->gateway->getGateway('first');
        $gate_2 = $this->gateway->getGateway('first', true);

        $this->assertNotSame($gate_1, $gate_2);
    }

    /**
     * @covers SMSKrank\GatewayFactory::clearPool
     */
    public function testClearPoolFull()
    {
        $gate_1 = $this->gateway->getGateway('first');
        $this->gateway->clearPool();
        $gate_2 = $this->gateway->getGateway('first');

        $this->assertNotSame($gate_1, $gate_2);
    }

    /**
     * @covers SMSKrank\GatewayFactory::clearPool
     */
    public function testClearPoolPartial()
    {
        $first_1  = $this->gateway->getGateway('first');
        $second_1 = $this->gateway->getGateway('second');
        $this->gateway->clearPool('second');
        $first_2  = $this->gateway->getGateway('first');
        $second_2 = $this->gateway->getGateway('second');

        $this->assertSame($first_1, $first_2);
        $this->assertNotSame($second_1, $second_2);
    }
}
