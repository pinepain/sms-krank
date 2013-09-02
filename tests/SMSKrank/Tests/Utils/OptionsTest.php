<?php


namespace SMSKrank\Tests\Utils;

use SMSKrank\Utils\Exceptions\OptionsException;
use SMSKrank\Utils\Options;

class OptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Options
     */
    private $o;

    protected function setUp()
    {
        $this->o = new Options();
    }

    /**
     * @covers \SMSKrank\Utils\Options::__construct
     * @covers \SMSKrank\Utils\Options::all
     */
    public function testConstruct()
    {
        $options = array('foo' => 'bar', 'baz' => 'tar');

        $o = new Options($options);

        $this->assertSame($options, $o->all());
    }

    /**
     * @covers \SMSKrank\Utils\Options::get
     * @covers \SMSKrank\Utils\Options::replace
     */
    public function testGet()
    {
        $this->o->replace(array('foo' => 'bar', 'baz' => 'tar'));

        $this->assertSame('bar', $this->o->get('foo'));
        $this->assertSame('bar', $this->o->get('foo', 'baz'));
        $this->assertSame('tar', $this->o->get('baz'));
        $this->assertSame('tar', $this->o->get('baz', 'bar'));
    }

    /**
     * @covers \SMSKrank\Utils\Options::get
     */
    public function testGetDefault()
    {
        $this->assertSame(null, $this->o->get('foo'));
        $this->assertSame('bar', $this->o->get('foo', 'bar'));
        $this->assertSame(array(), $this->o->get('foo', array()));
    }

    /**
     * @covers \SMSKrank\Utils\Options::set
     */
    public function testSetOneOverride()
    {
        $this->o->set('foo', 'bar');
        $this->assertSame('bar', $this->o->get('foo'));
        $this->o->set('foo', 'baz');
        $this->assertSame('baz', $this->o->get('foo'));
    }

    /**
     * @covers \SMSKrank\Utils\Options::set
     */
    public function testSetOneNoOverride()
    {
        $this->o->set('foo', 'bar');
        $this->assertSame('bar', $this->o->get('foo'));
        $this->o->set('foo', 'baz', false);
        $this->assertSame('bar', $this->o->get('foo'));
    }

    /**
     * @covers \SMSKrank\Utils\Options::set
     */
    public function testSetArrayOverride()
    {
        $this->o->set(array('foo' => 'bar', 'baz' => 'tar'));
        $this->assertSame('bar', $this->o->get('foo'));
        $this->assertSame('tar', $this->o->get('baz'));

        $this->o->set(array('foo' => 'tar', 'maz' => 'ars'));

        $this->assertSame('tar', $this->o->get('foo'));
        $this->assertSame('tar', $this->o->get('baz'));
        $this->assertSame('ars', $this->o->get('maz'));
    }

    /**
     * @covers \SMSKrank\Utils\Options::set
     */
    public function testSetArrayNoOverride()
    {
        $this->o->set(array('foo' => 'bar', 'baz' => 'tar'));
        $this->assertSame('bar', $this->o->get('foo'));
        $this->assertSame('tar', $this->o->get('baz'));

        $this->o->set(array('foo' => 'tar', 'maz' => 'ars'), false);

        $this->assertSame('bar', $this->o->get('foo'));
        $this->assertSame('tar', $this->o->get('baz'));
        $this->assertSame('ars', $this->o->get('maz'));
    }


    /**
     * @covers \SMSKrank\Utils\Options::set
     */
    public function testSetOptionsOverride()
    {
        $this->o->set(new Options(array('foo' => 'bar', 'baz' => 'tar')));
        $this->assertSame('bar', $this->o->get('foo'));
        $this->assertSame('tar', $this->o->get('baz'));

        $this->o->set(new Options(array('foo' => 'tar', 'maz' => 'ars')));

        $this->assertSame('tar', $this->o->get('foo'));
        $this->assertSame('tar', $this->o->get('baz'));
        $this->assertSame('ars', $this->o->get('maz'));
    }

    /**
     * @covers \SMSKrank\Utils\Options::set
     */
    public function testSetOptionsNoOverride()
    {
        $this->o->set(new Options(array('foo' => 'bar', 'baz' => 'tar')));
        $this->assertSame('bar', $this->o->get('foo'));
        $this->assertSame('tar', $this->o->get('baz'));

        $this->o->set(new Options(array('foo' => 'tar', 'maz' => 'ars')), false);

        $this->assertSame('bar', $this->o->get('foo'));
        $this->assertSame('tar', $this->o->get('baz'));
        $this->assertSame('ars', $this->o->get('maz'));
    }

    /**
     * @covers \SMSKrank\Utils\Options::has
     */
    public function testHasOne()
    {
        $this->o->set(array('foo' => 'bar', 'baz' => 'tar'));
        $this->assertTrue($this->o->has('foo'));
        $this->assertFalse($this->o->has('nonexistent'));
    }

    /**
     * @covers \SMSKrank\Utils\Options::has
     */
    public function testHasManyArgs()
    {
        $this->o->set(array('foo' => 'bar', 'baz' => 'tar'));
        $this->assertTrue($this->o->has('foo', 'baz'));
        $this->assertFalse($this->o->has('foo', 'baz', 'nonexistent'));
    }

    /**
     * @covers \SMSKrank\Utils\Options::has
     */
    public function testHasManyArray()
    {
        $this->o->set(array('foo' => 'bar', 'baz' => 'tar'));
        $this->assertTrue($this->o->has(array('foo', 'baz')));
        $this->assertFalse($this->o->has(array('foo', 'baz', 'nonexistent')));
    }

    /**
     * @covers \SMSKrank\Utils\Options::has
     */
    public function testHasManyOptions()
    {
        $this->o->set(array('foo' => 'bar', 'baz' => 'tar'));
        $this->assertTrue($this->o->has(new Options(array('foo', 'baz'))));
        $this->assertFalse($this->o->has(new Options(array('foo', 'baz', 'nonexistent'))));
    }

    /**
     * @covers                   \SMSKrank\Utils\Options::requires
     */
    public function testRequiresOk()
    {
        $this->o->set(array('foo' => 'bar', 'baz' => 'tar'));
        $this->o->requires('foo');
    }

    /**
     * @covers                   \SMSKrank\Utils\Options::requires
     *
     * @expectedException \SMSKrank\Utils\Exceptions\OptionsException
     * @expectedExceptionMessage Missed required option
     */
    public function testRequires()
    {
        $this->o->set(array('foo' => 'bar', 'baz' => 'tar'));
        $this->o->requires('nonexistent');
    }

    /**
     * @covers                   \SMSKrank\Utils\Options::requires
     *
     * @expectedException \SMSKrank\Utils\Exceptions\OptionsException
     * @expectedExceptionMessage Missed required option
     */
    public function testRequiresManyArgsFirst()
    {
        $this->o->set(array('foo' => 'bar', 'baz' => 'tar'));
        $this->o->requires('nonexistent', 'foo', 'bar');
    }

    /**
     * @covers                   \SMSKrank\Utils\Options::requires
     *
     * @expectedException \SMSKrank\Utils\Exceptions\OptionsException
     * @expectedExceptionMessage Missed required option
     */
    public function testRequiresManyArgsInside()
    {
        $this->o->set(array('foo' => 'bar', 'baz' => 'tar'));
        $this->o->requires('foo', 'nonexistent', 'baz');
    }

    /**
     * @covers                   \SMSKrank\Utils\Options::requires
     *
     * @expectedException \SMSKrank\Utils\Exceptions\OptionsException
     * @expectedExceptionMessage Missed required option
     */
    public function testRequiresManyArgsLast()
    {
        $this->o->set(array('foo' => 'bar', 'baz' => 'tar'));
        $this->o->requires('foo', 'baz', 'nonexistent');
    }

    /**
     * @covers                   \SMSKrank\Utils\Options::requires
     *
     * @expectedException \SMSKrank\Utils\Exceptions\OptionsException
     * @expectedExceptionMessage Missed required option
     */
    public function testRequiresManyArrayFirst()
    {
        $this->o->set(array('foo' => 'bar', 'baz' => 'tar'));
        $this->o->requires(array('nonexistent'));
    }

    /**
     * @covers                   \SMSKrank\Utils\Options::requires
     *
     * @expectedException \SMSKrank\Utils\Exceptions\OptionsException
     * @expectedExceptionMessage Missed required option
     */
    public function testRequiresManyArrayInside()
    {
        $this->o->set(array('foo' => 'bar', 'baz' => 'tar'));
        $this->o->requires(array('foo', 'nonexistent', 'baz'));
    }

    /**
     * @covers                   \SMSKrank\Utils\Options::requires
     *
     * @expectedException \SMSKrank\Utils\Exceptions\OptionsException
     * @expectedExceptionMessage Missed required option
     */
    public function testRequiresManyArrayLast()
    {
        $this->o->set(array('foo' => 'bar', 'baz' => 'tar'));
        $this->o->requires(array('foo', 'baz', 'nonexistent'));
    }

    /**
     * @covers                   \SMSKrank\Utils\Options::requires
     *
     * @expectedException \SMSKrank\Utils\Exceptions\OptionsException
     * @expectedExceptionMessage Missed required option
     */
    public function testRequiresManyOptionsFirst()
    {
        $this->o->set(array('foo' => 'bar', 'baz' => 'tar'));
        $this->o->requires(new Options(array('nonexistent')));
    }

    /**
     * @covers                   \SMSKrank\Utils\Options::requires
     *
     * @expectedException \SMSKrank\Utils\Exceptions\OptionsException
     * @expectedExceptionMessage Missed required option
     */
    public function testRequiresManyOptionsInside()
    {
        $this->o->set(array('foo' => 'bar', 'baz' => 'tar'));
        $this->o->requires(new Options(array('foo', 'nonexistent', 'baz')));
    }

    /**
     * @covers                   \SMSKrank\Utils\Options::requires
     *
     * @expectedException \SMSKrank\Utils\Exceptions\OptionsException
     * @expectedExceptionMessage Missed required option
     */
    public function testRequiresManyOptionsLast()
    {
        $this->o->set(array('foo' => 'bar', 'baz' => 'tar'));
        $this->o->requires(new Options(array('foo', 'baz', 'nonexistent')));
    }

    /**
     * @covers \SMSKrank\Utils\Options::del
     */
    public function testDel()
    {
        $this->o->set(array('foo' => 'bar', 'baz' => 'tar'));

        $this->assertTrue($this->o->has('foo'));
        $this->o->del('foo');
        $this->assertFalse($this->o->has('foo'));
    }

    /**
     * @covers \SMSKrank\Utils\Options::del
     */
    public function testDelManyArgs()
    {
        $this->o->set(array('foo' => 'bar', 'baz' => 'tar'));

        $this->assertTrue($this->o->has('foo', 'baz'));
        $this->o->del('foo', 'baz');
        $this->assertFalse($this->o->has('foo', 'baz'));
    }

    /**
     * @covers \SMSKrank\Utils\Options::del
     */
    public function testDelManyArray()
    {
        $this->o->set(array('foo' => 'bar', 'baz' => 'tar'));
        $this->assertTrue($this->o->has(array('foo', 'baz')));
        $this->o->del(array('foo', 'baz'));
        $this->assertFalse($this->o->has(array('foo', 'baz')));
    }

    /**
     * @covers \SMSKrank\Utils\Options::del
     */
    public function testDelManyOptions()
    {
        $this->o->set(array('foo' => 'bar', 'baz' => 'tar'));
        $this->assertTrue($this->o->has(new Options(array('foo', 'baz'))));
        $this->o->del(new Options(array('foo', 'baz')));
        $this->assertFalse($this->o->has(new Options(array('foo', 'baz'))));
    }

    /**
     * @covers \SMSKrank\Utils\Options::replace
     */
    public function testReplaceOne()
    {
        $this->o->set(array('foo' => 'bar', 'baz' => 'tar'));

        $this->o->replace('foo');
        $this->assertSame(array('foo' => null), $this->o->all());
    }

    /**
     * @covers \SMSKrank\Utils\Options::replace
     */
    public function testReplaceManyArgs()
    {
        $this->o->set(array('foo' => 'bar', 'baz' => 'tar'));

        $this->o->replace('foo', 'baz');
        $this->assertSame(array('foo' => null, 'baz' => null), $this->o->all());
    }

    /**
     * @covers \SMSKrank\Utils\Options::replace
     */
    public function testReplaceArray()
    {
        $this->o->set(array('foo' => 'bar', 'baz' => 'tar'));

        $this->o->replace(array('foo' => 'new', 'baz' => 'values'));
        $this->assertSame(array('foo' => 'new', 'baz' => 'values'), $this->o->all());

        $this->o->replace(array());
        $this->assertEmpty($this->o->all());
    }

    /**
     * @covers \SMSKrank\Utils\Options::replace
     */
    public function testReplaceOptions()
    {
        $this->o->set(array('foo' => 'bar', 'baz' => 'tar'));

        $this->o->replace(new Options(array('foo' => 'new', 'baz' => 'values')));
        $this->assertSame(array('foo' => 'new', 'baz' => 'values'), $this->o->all());

        $this->o->replace(new Options());
        $this->assertEmpty($this->o->all());
    }
}
