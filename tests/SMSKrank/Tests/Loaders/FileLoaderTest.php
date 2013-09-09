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
                    '.dot.yaml'   => Yaml::dump($d_dot),
                    'ext.yml'     => Yaml::dump($a_ext),
                    'first.yaml'  => Yaml::dump($a_first),
                    'second.yaml' => Yaml::dump($a_second),
                ),
            ),
            'nested-two-levels'  => array(
                '.dot.yaml'   => Yaml::dump($d_dot),
                'ext.yml'     => Yaml::dump($a_ext),
                'first.yaml'  => Yaml::dump($a_first),
                'second.yaml' => Yaml::dump($a_second),

                'nested'      => array(
                    '.dot.yaml'   => Yaml::dump($d_dot),
                    'ext.yml'     => Yaml::dump($a_ext),
                    'first.yaml'  => Yaml::dump($a_first),
                    'second.yaml' => Yaml::dump($a_second),

                    'second'      => array(
                        '.dot.yaml'   => Yaml::dump($d_dot),
                        'ext.yml'     => Yaml::dump($a_ext),
                        'first.yaml'  => Yaml::dump($a_first),
                        'second.yaml' => Yaml::dump($a_second),
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
     * @covers \SMSKrank\Loaders\FileLoader::loadFile
     * @covers \SMSKrank\Loaders\FileLoader::loadDirectory
     */
    public function testLoaderEmptyDirectory()
    {
        $loader = new FileLoader(vfsStream::url('source/empty'), $this->getParserStub());

        $expected = array();

        $this->assertEquals($expected, $loader->load('/*'));
    }

    /**
     * @covers \SMSKrank\Loaders\FileLoader::load
     * @covers \SMSKrank\Loaders\FileLoader::loadFile
     * @covers \SMSKrank\Loaders\FileLoader::loadDirectory
     */
    public function testLoaderPlainDirectory()
    {
        $loader = new FileLoader(vfsStream::url('source/plain'), $this->getParserStub());

        $expected = array('first' => array('first'), 'second' => array('second'));

        $this->assertEquals($expected, $loader->load('/*'));
    }

    /**
     * @covers \SMSKrank\Loaders\FileLoader::load
     * @covers \SMSKrank\Loaders\FileLoader::loadFile
     * @covers \SMSKrank\Loaders\FileLoader::loadDirectory
     */
    public function testLoaderNestedDirectoriesDefault()
    {
        $loader = new FileLoader(vfsStream::url('source/nested-one-level'), $this->getParserStub());

        $expected = array('first' => array('first'), 'second' => array('second'));

        // second level should not be loaded
        $this->assertEquals($expected, $loader->load('/*'));
    }

    /**
     * @covers \SMSKrank\Loaders\FileLoader::load
     * @covers \SMSKrank\Loaders\FileLoader::loadFile
     * @covers \SMSKrank\Loaders\FileLoader::loadDirectory
     */
    public function testLoaderNestedDirectoriesNested()
    {
        $loader = new FileLoader(vfsStream::url('source/nested-one-level'), $this->getParserStub());

        $expected = array('nested/first' => array('first'), 'nested/second' => array('second'));

        // first level should not be loaded
        $this->assertEquals($expected, $loader->load('nested/*'));
    }

    /**
     * @covers \SMSKrank\Loaders\FileLoader::load
     * @covers \SMSKrank\Loaders\FileLoader::loadFile
     * @covers \SMSKrank\Loaders\FileLoader::loadDirectory
     */
    public function testLoaderNestedDirectoriesDefaultAndNested()
    {
        $loader = new FileLoader(vfsStream::url('source/nested-one-level'), $this->getParserStub());

        $expected = array(
            'first'        => array('first'), 'second' => array('second'),
            'nested/first' => array('first'), 'nested/second' => array('second')
        );
        $loader->load('/*');
        $loader->load('nested/*');

        $this->assertEquals($expected, $loader->get());
    }

    /**
     * @covers \SMSKrank\Loaders\FileLoader::load
     * @covers \SMSKrank\Loaders\FileLoader::loadFile
     * @covers \SMSKrank\Loaders\FileLoader::loadDirectory
     * @covers \SMSKrank\Loaders\FileLoader::get
     */
    public function testLoaderOneShot()
    {
        $loader = new FileLoader(vfsStream::url('source/plain'), $this->getParserStub());

        $this->assertEmpty($loader->get());

        $expected = array('first' => array('first'));
        $this->assertEquals($expected, $loader->load('first', true));

        $this->assertEmpty($loader->get());
    }

    /**
     * @covers \SMSKrank\Loaders\FileLoader::load
     * @covers \SMSKrank\Loaders\FileLoader::loadFile
     * @covers \SMSKrank\Loaders\FileLoader::loadDirectory
     */
    public function testLoaderPlainWithIgnoredDirectory()
    {
        $loader = new FileLoader(vfsStream::url('source/plain-with-ignored'), $this->getParserStub());

        $expected = array('first' => array('first'), 'second' => array('second'));

        $this->assertEquals($expected, $loader->load('/*'));
    }

    /**
     * @covers \SMSKrank\Loaders\FileLoader::load
     * @covers \SMSKrank\Loaders\FileLoader::loadFile
     * @covers \SMSKrank\Loaders\FileLoader::loadDirectory
     */
    public function testLoaderDotFile()
    {
        $loader = new FileLoader(vfsStream::url('source/plain-with-ignored'), $this->getParserStub());

        $expected = array('.dot' => array('dot'));

        $this->assertEquals($expected, $loader->load('.dot'));
    }

    /**
     * @covers                   \SMSKrank\Loaders\FileLoader::load
     * @covers                   \SMSKrank\Loaders\FileLoader::loadFile
     * @covers                   \SMSKrank\Loaders\FileLoader::loadDirectory
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
     * @covers                   \SMSKrank\Loaders\FileLoader::loadFile
     * @covers                   \SMSKrank\Loaders\FileLoader::loadDirectory
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
     * @covers                   \SMSKrank\Loaders\FileLoader::loadFile
     * @covers                   \SMSKrank\Loaders\FileLoader::loadDirectory
     *
     * @expectedException        \SMSKrank\Loaders\Exceptions\LoaderException
     * @expectedExceptionMessage File 'vfs://source/invalid/garbaged.yaml' contains garbage
     */
    public function testLoaderNonReadable()
    {
        $loader = new FileLoader(vfsStream::url('source/invalid'), $this->getParserStub());

        $loader->load('garbaged');
    }

    /**
     * @covers                   \SMSKrank\Loaders\FileLoader::load
     * @covers                   \SMSKrank\Loaders\FileLoader::loadFile
     * @covers                   \SMSKrank\Loaders\FileLoader::loadDirectory
     *
     * @expectedException        \SMSKrank\Loaders\Exceptions\LoaderException
     * @expectedExceptionMessage File 'vfs://source/invalid/nonexistent.yaml' does not exists
     */
    public function testLoaderNonExistentFile()
    {
        $loader = new FileLoader(vfsStream::url('source/invalid'), $this->getParserStub());

        $loader->load('nonexistent');
    }

    /**
     * @covers                   \SMSKrank\Loaders\FileLoader::load
     * @covers                   \SMSKrank\Loaders\FileLoader::loadFile
     * @covers                   \SMSKrank\Loaders\FileLoader::loadDirectory
     *
     * @expectedException        \SMSKrank\Loaders\Exceptions\LoaderException
     * @expectedExceptionMessage Directory 'vfs://source/invalid/nonexistent' does not exists
     */
    public function testLoaderNonExistentDirectory()
    {
        $loader = new FileLoader(vfsStream::url('source/invalid'), $this->getParserStub());

        $loader->load('nonexistent/*');
    }

    /**
     * @covers \SMSKrank\Loaders\FileLoader::get
     */
    public function testGetLoaded()
    {
        $loader = new FileLoader(vfsStream::url('source/plain'), $this->getParserStub());

        $loader->load('/*');

        vfsStream::setup('source'); // reset file structure
        $this->assertFalse(is_dir(vfsStream::url('source/plain'))); //ensure that fs reset

        $this->assertSame(array('first'), $loader->get('first'));
        $this->assertSame(array('second'), $loader->get('second'));

        // get all loaded
        $this->assertSame(array('first' => array('first'), 'second' => array('second')), $loader->get());
    }

    /**
     * @covers \SMSKrank\Loaders\FileLoader::get
     */
    public function testGetOnDemand()
    {
        $loader = new FileLoader(vfsStream::url('source/nested-one-level'), $this->getParserStub());

        $this->assertSame(array('first'), $loader->get('first'));
        $this->assertSame(array('first' => array('first')), $loader->get());

        $this->assertSame(array('second'), $loader->get('second'));
        $this->assertSame(array('first' => array('first'), 'second' => array('second')), $loader->get());

        $this->assertSame(array('first'), $loader->get('nested/first'));
        $this->assertSame(
            array('first' => array('first'), 'nested/first' => array('first'), 'second' => array('second')),
            $loader->get()
        );
    }

    /**
     * @covers \SMSKrank\Loaders\FileLoader::get
     */
    public function testGetOneShot()
    {
        $loader = new FileLoader(vfsStream::url('source/nested-one-level'), $this->getParserStub());

        $this->assertSame(array('first'), $loader->get('first', true));
        $this->assertEmpty($loader->get());
        $this->assertSame(array('second'), $loader->get('second', true));
        $this->assertEmpty($loader->get());
        $this->assertSame(array('first'), $loader->get('nested/first', true));
        $this->assertEmpty($loader->get());
    }

    /**
     * @covers \SMSKrank\Loaders\FileLoader::get
     */
    public function testGetOneShotOnExistent()
    {
        $loader = new FileLoader(vfsStream::url('source/plain'), $this->getParserStub());

        $this->assertSame(array('first'), $loader->get('first'));
        $this->assertSame(array('first' => array('first')), $loader->get());

        $structure = array(
            'plain' => array(
                'first.yaml' => Yaml::dump(array('first was changed')),
            ),
        );

        vfsStream::setup('source', null, $structure); // reset fs to custom one;

        // with one-shot file fill be re-read again
        $this->assertSame(array('first was changed'), $loader->get('first', true));

        // but previously loaded content will not be touched
        $this->assertSame(array('first' => array('first')), $loader->get());
    }

    /**
     * @covers \SMSKrank\Loaders\FileLoader::has
     */
    public function testHasOnEmpty()
    {
        $loader = new FileLoader(vfsStream::url('source/nested-two-levels'), $this->getParserStub());

        $this->assertEmpty($loader->get());

        $this->assertTrue($loader->has('first'));
        $this->assertTrue($loader->has('second'));

        // can be found, while just masked with dot
        $this->assertTrue($loader->has('.dot'));

        $this->assertFalse($loader->has('ext'));
        $this->assertFalse($loader->has('nonexistent'));

        $this->assertTrue($loader->has('nested/first'));
        $this->assertTrue($loader->has('nested/second'));

        // can be found, while just masked with dot
        $this->assertTrue($loader->has('nested/.dot'));

        $this->assertFalse($loader->has('nested/ext'));
        $this->assertFalse($loader->has('nested/nonexistent'));

        $this->assertTrue($loader->has('nested/second/first'));
        $this->assertTrue($loader->has('nested/second/second'));

        // can be found, while just masked with dot
        $this->assertTrue($loader->has('nested/second/.dot'));

        $this->assertFalse($loader->has('nested/second/ext'));
        $this->assertFalse($loader->has('nested/second/nonexistent'));

        $this->assertFalse($loader->has('foo/bar/baz'));

        $this->assertEmpty($loader->get());
    }

    /**
     * @covers \SMSKrank\Loaders\FileLoader::has
     */
    public function testHasOnLoaded()
    {
        $loader = new FileLoader(vfsStream::url('source/nested-two-levels'), $this->getParserStub());

        $loader->load('/*');
        $loader->load('nested/*');
        $loader->load('nested/second/*');

        vfsStream::setup('source'); // reset file structure
        $this->assertFalse(is_dir(vfsStream::url('source/nested-two-levels'))); //ensure that fs reset

        $this->assertTrue($loader->has('first'));
        $this->assertTrue($loader->has('second'));

        // cannot be found, while fs reset and dot-masked are not loaded by default with wildcard
        $this->assertFalse($loader->has('.dot'));

        $this->assertFalse($loader->has('ext'));
        $this->assertFalse($loader->has('nonexistent'));

        $this->assertTrue($loader->has('nested/first'));
        $this->assertTrue($loader->has('nested/second'));

        // cannot be found, while fs reset and dot-masked are not loaded by default with wildcard
        $this->assertFalse($loader->has('nested/.dot'));

        $this->assertFalse($loader->has('nested/ext'));
        $this->assertFalse($loader->has('nested/nonexistent'));

        $this->assertTrue($loader->has('nested/second/first'));
        $this->assertTrue($loader->has('nested/second/second'));

        // cannot be found, while fs reset and dot-masked are not loaded by default with wildcard
        $this->assertFalse($loader->has('nested/second/.dot'));

        $this->assertFalse($loader->has('nested/second/ext'));
        $this->assertFalse($loader->has('nested/second/nonexistent'));

        $this->assertFalse($loader->has('foo/bar/baz'));
    }

    /**
     * @covers \SMSKrank\Loaders\FileLoader::remove
     */
    public function testRemoveAll()
    {
        $loader = new FileLoader(vfsStream::url('source/plain'), $this->getParserStub());

        $loader->load('/*');

        $this->assertNotEmpty($loader->get());
        $loader->remove();
        $this->assertEmpty($loader->get());
    }

    /**
     * @covers \SMSKrank\Loaders\FileLoader::remove
     */
    public function testRemoveSpecific()
    {
        $loader = new FileLoader(vfsStream::url('source/plain'), $this->getParserStub());

        $loader->load('/*');

        $this->assertArrayHasKey('first', $loader->get());
        $loader->remove('first');

        $this->assertArrayNotHasKey('first', $loader->get());
        // but we still can reach 'first' by loading it again
        $this->assertTrue($loader->has('first'));

        $this->assertNotEmpty($loader->get());

        $expected = $loader->get();

        $loader->remove('nonexistent');

        $this->assertSame($expected, $loader->get());
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
