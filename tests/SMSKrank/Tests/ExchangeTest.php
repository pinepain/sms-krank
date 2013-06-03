<?php
/**
 * @author  zaq178miami
 * @created 6/2/13 10:34 PM
 */

namespace SMSKrank\Tests;


use SMSKrank\Directory;
use SMSKrank\Exchange;
use SMSKrank\GatewayFactory;
use SMSKrank\Message;
use SMSKrank\PhoneNumber;
use SMSKrank\PhoneNumberDetailed;
use SMSKrank\Utils\GatewaysLoader;
use SMSKrank\Utils\GatewaysMapLoader;
use SMSKrank\Utils\ZonesLoader;

class ExchangeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Exchange
     */
    private $exchange;

    private $exceptionClass = "\\SMSKrank\\Exceptions\\ExchangeException";

    protected function setUp()
    {
        $data_dir = __DIR__ . '/../../data/';

        $zones_loader = new ZonesLoader($data_dir . '/zones/zones-for-exchange/');
//        $zones_loader->load();
        $gates_loader = new GatewaysLoader($data_dir . '/gates/gates-for-exchange/');
//        $gates_loader->load();
//        $maps_loader = new GatewaysMapLoader($data_dir . '/maps/maps-for-exchange/', $gates_loader); // perform additional validation
        $maps_loader = new GatewaysMapLoader($data_dir . '/maps/maps-for-exchange/'); // no additional validation
//        $maps_loader->load();

        $gates_factory = new GatewayFactory($gates_loader);
        $directory     = new Directory($zones_loader);

        $this->exchange = new Exchange($maps_loader, $gates_factory, $directory);
    }

    public function providerSendSuccess()
    {
        // default output format for EchoDude gate is '{name}: {phone} - "{message}" at {schedule}' for scheduled messages
        // and '{name}: {phone} - "{message}" immediately' for messages that sends without schedule time
        $data = array(
            // US
            array(
                'Harley Davidson: 12395555155 - "Hello Marlboro Man, I am in Florida" at 2013-06-02T13:23:34-0700',
                '+1 239 555-51-55',
                'Hello Marlboro Man, I am in Florida',
                '2013-06-02T13:23:34-0700'
            ),
            array(
                'Marlboro Man: 13055555255 - "Hello Harley Davidson, I am in Florida too" at 2013-06-02T13:24:56-0700',
                '+1 305 555-52-55',
                'Hello Harley Davidson, I am in Florida too',
                '2013-06-02T13:24:56-0700'
            ),
            array(
                'Chuck Norris: 15555555555 - "Hello guys, I\'m in Florida too. Let\'s kick some asses!" immediately',
                '+1 555 555-55-55',
                'Hello guys, I\'m in Florida too. Let\'s kick some asses!',
                null
            ),
            // CA
            array(
                'Paul Bunyan: 12365555155 - "Hello Marlboro Man from Canada! How are you?" at 2013-06-02T14:01:02-0700',
                '+1 236 555-51-55',
                'Hello Marlboro Man from Canada! How are you?',
                '2013-06-02T14:01:02-0700'
            ),
            array(
                'Marlboro Man: 14185555255 - "Hey Payl Bunyan! I just saw Chuck Norris!" at 2013-06-02T14:05:18-0700',
                '+1 418 555-52-55',
                'Hey Payl Bunyan! I just saw Chuck Norris!',
                '2013-06-02T14:05:18-0700'
            ),
            array(
                'Chuck Norris: 11115555555 - "Let\'s rock!" immediately',
                '+1 111 555-55-55',
                'Let\'s rock!',
                null
            ),
            // GB
            array(
                'Knight: 445555555555 - "Show must go on!" at 2013-06-02T16:51:25-0700',
                '+44 555 555-55-55',
                'Show must go on!',
                '2013-06-02T16:51:25-0700'
            ),
            // DE
            array(
                'Ampelmennchen: 495555555555 - "Chuck Norris hat bis Unendlich gezählt. Zwei Mal." at 2013-06-02T16:55:22-0700',
                '+49 555 555-55-55',
                'Chuck Norris hat bis Unendlich gezählt. Zwei Mal.',
                '2013-06-02T16:55:22-0700'
            ),
        );

        foreach ($data as &$set) {
            $set[1] = new PhoneNumber($set[1]);
            $set[2] = new Message($set[2]);

            if ($set[3]) {
                $set['3'] = new \DateTime($set[3]);
            }
        }

        return $data;
    }


    public function providerSendFailure()
    {
        // default output format for EchoDude gate is '{name}: {phone} - "{message}" at {schedule}' for scheduled messages
        // and '{name}: {phone} - "{message}" immediately' for messages that sends without schedule time
        $data = array(

            // RO, no gate at all
            array(
                'Failed to send message (unable to load country)',
                new PhoneNumber('+40 555 555-55-55')
            ),
            // CH, has not enough money
            array(
                'Failed to send message (unable to pick working gate)',
                new PhoneNumber('+41 555 555-55-55')
            ),
            // FICTIONAL
            array(
                'Failed to send message (phone number is not supported)',
                new PhoneNumberDetailed('+41 555 555-55-55', array(), array(), array()),
            ),
        );

        return $data;
    }

    /**
     * @covers       \SMSKrank\Exchange::__construct
     * @covers       \SMSKrank\Exchange::send
     * @covers       \SMSKrank\Exchange::array_intersect_assoc_recursive
     *
     * @dataProvider providerSendSuccess
     */
    public function testSendSuccess($expected_output, PhoneNumber $number, Message $message, \DateTime $schedule = null)
    {
        $this->expectOutputString($expected_output);
        $this->exchange->send($number, $message, $schedule);
    }

    /**
     * @covers       \SMSKrank\Exchange::__construct
     * @covers       \SMSKrank\Exchange::send
     * @covers       \SMSKrank\Exchange::array_intersect_assoc_recursive
     *
     * @dataProvider providerSendFailure
     */
    public function testSendFailure($message, PhoneNumber $number)
    {
        $this->setExpectedException($this->exceptionClass, $message);
        $this->exchange->send($number, new Message('no message, test should fail'), null);
    }


}
