<?php

namespace SMSKrank\Tests\Utils;

use SMSKrank\Utils\GatewaysLoader;

class GatewaysLoaderTest extends \PHPUnit_Framework_TestCase
{
    private $providerExpectedException = '\SMSKrank\Utils\Exceptions\LoaderException';

    public function providerSources()
    {
        $dir = __DIR__ . '/../../../data/gates/gates-valid';

        return array(
            array($dir . '/nonexistent.file', "Source does not exists"),
            array($dir . '/nonexistent/directory/', "Source does not exists"),
            array($dir . '/', null),
            array($dir . '/first.yaml', null),
        );
    }

    public function providerInvalidYamls()
    {
        $dir = __DIR__ . '/../../../data/gates/gates-invalid';

        return array(
            array(__DIR__ . '/../../../data/gates/gates-valid/first.yaml', "Source directory is file", 'second'),
            array($dir . '/garbage.yaml', "Garbage in container file 'garbage'"),
            array($dir . '/invalid-args.yaml', "Gateway class arguments has wrong type"),
            array($dir . '/invalid-class.yaml', "Gateway class doesn't exists"),
            array($dir . '/missed-args.yaml', "Missed gateway arguments"),
            array($dir . '/missed-class.yaml', "Missed gateway class"),
            array($dir . '/mutiple-gates.yaml', "One file - one gate"),
            array($dir . '/notimplements-class.yaml', "doesn't implement standard gate interface"),
            array($dir . '/missed-arg-in-args.yaml', "Missed argument 'login'"),
            array($dir . '/wrong-name.yaml', "Wrong gateway name in file"),
        );
    }

    public function providerValidYamls()
    {
        $dir = __DIR__ . '/../../../data/gates/gates-valid';

        $first = array(
            'class' => '\SMSKrank\Helpers\Gateways\BlackHole',
            'args'  => array('login-first', 'pswd-first')
        );

        $second = array(
            'class' => '\SMSKrank\Helpers\Gateways\BlackHole',
            'args'  => array('login-second', 'default')
        );

        $ignored = array(
            'class' => '\SMSKrank\Helpers\Gateways\BlackHole',
            'args'  => array('login-ignored', 'paswd-ignored')
        );

        $first_joint = array(
            'class' => '\SMSKrank\Helpers\Gateways\BlackHole',
            'args'  => array('login-first-joint', 'pswd-first-joint')
        );

        $second_joint = array(
            'class' => '\SMSKrank\Helpers\Gateways\BlackHole',
            'args'  => array('login-second-joint', 'default')
        );

        $first_joint_override = array(
            'class' => '\SMSKrank\Helpers\Gateways\BlackHole',
            'args'  => array('login-first-joint-override', 'pswd-first-joint-override')
        );

        return array(
            array($dir . '/first.yaml', array('first' => $first)),
            array($dir . '/second.yaml', array('second' => $second)),
            array($dir . '/.ignored-dot.yaml', array('.ignored-dot' => $ignored)),
            array($dir . '/ignored-ext.yml', array('ignored-ext' => $ignored)),
            array($dir . '/', array('first' => $first, 'second' => $second)),
        );
    }

    /**
     * @covers                   \SMSKrank\Utils\GatewaysLoader::__construct
     *
     * @expectedException \PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage Missing argument
     */
    public function testConstructorDefault()
    {
        $d = new GatewaysLoader();
        $this->assertTrue(true);
    }

    /**
     * @covers       \SMSKrank\Utils\GatewaysLoader::__construct
     *
     * @dataProvider providerSources
     */
    public function testConstructor($file, $message)
    {
        if ($message) {
            $this->setExpectedException($this->providerExpectedException, $message);
        }

        $d = new GatewaysLoader($file);
        $this->assertTrue(true);
    }

    /**
     * @covers       \SMSKrank\Utils\GatewaysLoader::__construct
     * @covers       \SMSKrank\Utils\GatewaysLoader::load
     * @covers       \SMSKrank\Utils\GatewaysLoader::postLoad
     *
     * @dataProvider providerInvalidYamls
     */
    public function testLoadInvalid($file, $message, $group = null)
    {
        $this->setExpectedException($this->providerExpectedException, $message);

        $d = new GatewaysLoader($file);

        if ($group) {
            $loaded = $d->load($group);
        } else {
            $loaded = $d->load();
        }
    }

    /**
     * @covers       \SMSKrank\Utils\GatewaysLoader::__construct
     * @covers       \SMSKrank\Utils\GatewaysLoader::load
     * @covers       \SMSKrank\Utils\GatewaysLoader::postLoad
     *
     * @dataProvider providerValidYamls
     */
    public function testLoadValid($file, array $data, $group = null)
    {
        $d = new GatewaysLoader($file);

        if ($group) {
            $loaded = $d->load($group);
        } else {
            $loaded = $d->load();
        }

        $this->assertSame($data, $loaded);
    }
}
