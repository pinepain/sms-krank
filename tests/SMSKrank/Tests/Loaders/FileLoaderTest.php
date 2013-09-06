<?php

namespace SMSKrank\Tests\Loaders;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use SMSKrank\Loaders\FileLoader;
use Symfony\Component\Yaml\Yaml;

class FileLoaderTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $d_dot    = array('dot');
        $a_ext    = array('ext');
        $a_first  = array('first');
        $a_second = array('second');

        $structure = array(
            'file.test'          => 'failed before yaml parsing',
            'empty'              => array(),
            'plain'              => array(
                'first.yaml'  => Yaml::dump($a_first),
                'second.yaml' => Yaml::dump($a_second),
            ),
            'plain-with-ignored' => array(
                '.dot.yaml'   => Yaml::dump($d_dot),
                'ext.yml'     => Yaml::dump($a_ext),
                'first.yaml'  => Yaml::dump($a_first),
                'second.yaml' => Yaml::dump($a_second),
            ),
            'nested-one-level'   => array(
                '.dot.yaml'   => Yaml::dump($d_dot),
                'ext.yml'     => Yaml::dump($a_ext),
                'first.yaml'  => Yaml::dump($a_first),
                'second.yaml' => Yaml::dump($a_second),

                'nested'      => array(
                    '.nested_dot.yaml'   => Yaml::dump($d_dot),
                    'nested_ext.yml'     => Yaml::dump($a_ext),
                    'nested_first.yaml'  => Yaml::dump($a_first),
                    'nested_second.yaml' => Yaml::dump($a_second),
                ),
            ),
            'nested-two-levels'  => array(
                '.dot.yaml'   => Yaml::dump($d_dot),
                'ext.yml'     => Yaml::dump($a_ext),
                'first.yaml'  => Yaml::dump($a_first),
                'second.yaml' => Yaml::dump($a_second),

                'nested'      => array(
                    '.nested_dot.yaml'   => Yaml::dump($d_dot),
                    'nested_ext.yml'     => Yaml::dump($a_ext),
                    'nested_first.yaml'  => Yaml::dump($a_first),
                    'nested_second.yaml' => Yaml::dump($a_second),

                    'second'             => array(
                        '.second_dot.yaml'   => Yaml::dump($d_dot),
                        'second_ext.yml'     => Yaml::dump($a_ext),
                        'second_first.yaml'  => Yaml::dump($a_first),
                        'second_second.yaml' => Yaml::dump($a_second),
                    ),
                ),

            ),
            'invalid'            => array(
                'bad-extension.yml' => 'failed before yaml parsing',
                'non-readable.yaml' => 'failed before yaml parsing',
                'garbaged.yaml'     => Yaml::dump('garbage'),
            ),
        );

        $source = vfsStream::setup('source', null, $structure);

        $source->getChild('invalid/non-readable.yaml')->chmod(0000);
    }

    /**
     * @covers \SMSKrank\Loaders\FileLoader::__construct
     */
    public function testConstructSuccess()
    {
        new FileLoader(vfsStream::url('source'), $this->getParserStub());

        $this->assertTrue(true);
    }

    /**
     * @covers                   \SMSKrank\Loaders\FileLoader::__construct
     * @expectedException        \SMSKrank\Loaders\Exceptions\LoaderException
     * @expectedExceptionMessage Source does not exists
     */
    public function testConstructorFailureInvalidSource()
    {
        new FileLoader('nonexistent', $this->getParserStub());
    }

    /**
     * @covers                   \SMSKrank\Loaders\FileLoader::__construct
     * @expectedException        \SMSKrank\Loaders\Exceptions\LoaderException
     * @expectedExceptionMessage Source should be directory
     */
    public function testConstructorFailureSourceIsFile()
    {
        new FileLoader(vfsStream::url('source/file.test'), $this->getParserStub());
    }

    /**
     * @covers \SMSKrank\Loaders\FileLoader::load
     */
    public function testLoaderEmptyDirectory()
    {
        $loader = new FileLoader(vfsStream::url('source/empty'), $this->getParserStub());

        $expected = array();

        $this->assertEquals($expected, $loader->load());
    }

    /**
     * @covers \SMSKrank\Loaders\FileLoader::load
     */
    public function testLoaderPlainDirectory()
    {
        $loader = new FileLoader(vfsStream::url('source/plain'), $this->getParserStub());

        $expected = array('first' => array('first'), 'second' => array('second'));

        $this->assertEquals($expected, $loader->load());
    }

    /**
     * @covers \SMSKrank\Loaders\FileLoader::load
     */
    public function testLoaderPlainWithIgnoredDirectory()
    {
        $loader = new FileLoader(vfsStream::url('source/plain-with-ignored'), $this->getParserStub());

        $expected = array('first' => array('first'), 'second' => array('second'));

        $this->assertEquals($expected, $loader->load());
    }

    /**
     * @covers \SMSKrank\Loaders\FileLoader::load
     */
    public function testLoaderDotFile()
    {
        $loader = new FileLoader(vfsStream::url('source/plain-with-ignored'), $this->getParserStub());

        $expected = array('.dot' => array('dot'));

        $this->assertEquals($expected, $loader->load('.dot'));
    }

    /**
     * @covers                   \SMSKrank\Loaders\FileLoader::load
     *
     * @expectedException        \SMSKrank\Loaders\Exceptions\LoaderException
     * @expectedExceptionMessage File 'vfs://source/invalid/bad-extension.yaml' does not exists
     */
    public function testLoaderBadExtension()
    {
        $loader = new FileLoader(vfsStream::url('source/invalid'), $this->getParserStub());

        $loader->load('bad-extension');
    }

    /**
     * @covers                   \SMSKrank\Loaders\FileLoader::load
     *
     * @expectedException        \SMSKrank\Loaders\Exceptions\LoaderException
     * @expectedExceptionMessage File 'vfs://source/invalid/non-readable.yaml' is not readable
     */
    public function testLoaderGarbaged()
    {
        $loader = new FileLoader(vfsStream::url('source/invalid'), $this->getParserStub());

        $loader->load('non-readable');
    }

    /**
     * @covers                   \SMSKrank\Loaders\FileLoader::load
     *
     * @expectedException        \SMSKrank\Loaders\Exceptions\LoaderException
     * @expectedExceptionMessage File 'vfs://source/invalid/garbaged.yaml' contains garbage
     */
    public function testLoaderNonReadable()
    {
        $loader = new FileLoader(vfsStream::url('source/invalid'), $this->getParserStub());

        $loader->load('garbaged');
    }


    protected function getParserStub()
    {
        $stub = $this->getMock('SMSKrank\Loaders\Parsers\ParserInterface');

        $stub->expects($this->any())
            ->method('parse')
            ->will($this->returnArgument(0));

        return $stub;
    }

}
