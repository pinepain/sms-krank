<?php

namespace SMSKrank\Loaders;

use SMSKrank\Loaders\Exceptions\LoaderException;
use SMSKrank\Loaders\Parsers\ParserInterface;
use Symfony\Component\Yaml\Yaml;

class FileLoader implements LoaderInterface
{
    private $source;
    private $parser;

    private $container = array();

    public function __construct($source, ParserInterface $parser)
    {
        if (!file_exists($source)) {
            throw new LoaderException('Source does not exists');
        }

        if (is_file($source)) {
            throw new LoaderException("Source should be directory");
        }

        $this->source = $source;
        $this->parser = $parser;
    }

    protected function loadFile($path, $section)
    {
        if (!file_exists($path)) {
            throw new LoaderException("File '{$path}' does not exists");
        }

        if (!is_readable($path)) {
            throw new LoaderException("File '{$path}' is not readable");
        }

        $loaded = Yaml::parse(file_get_contents($path));

        if (!is_array($loaded)) {
            throw new LoaderException("File '{$path}' contains garbage");
        }

        return array($section => $this->parser->parse($loaded, $section, $this));
    }

    protected function loadDirectory($path)
    {
        if (!is_dir($path)) {
            throw new LoaderException("Directory '{$path}' does not exists");
        }

        $nested_sections = array_filter(
            scandir($path),
            function ($name) use ($path) {
                return $name[0] != '.' && substr($name, -5) == '.yaml' && is_file($path . DIRECTORY_SEPARATOR . $name);
            }
        );

        $container = array();

        foreach ($nested_sections as $s) {
            $container = $this->loadFile($path . DIRECTORY_SEPARATOR . $s, substr($s, 0, -5)) + $container;
        }

        return $container;
    }

    /**
     * @param string $section  Container name to load
     * @param bool   $one_shot Do not store result, just return it
     *
     * @return array
     */
    public function load($section, $one_shot = false)
    {

        $is_wildcard = (substr($section, -2) === '/*');

        if ($is_wildcard) {
            $section   = substr($section, 0, strlen($section) - 2);
            $container = $this->loadDirectory($this->source . DIRECTORY_SEPARATOR . $section);

            if (!empty($section)) {
                $plain_container = array();
                foreach ($container as $k => $v) {
                    $plain_container[$section . DIRECTORY_SEPARATOR . $k] = $v;
                }
                $container = $plain_container;
            }
        } else {
            $container_file = $this->source . DIRECTORY_SEPARATOR . $section . '.yaml';

            $container = $this->loadFile($container_file, $section);
        }

        if ($one_shot) {
            return $container;
        } else {
            $this->container = $container + $this->container;
            ksort($this->container);

            return $this->container;
        }
    }

    public function get($what = null, $one_shot = false)
    {
        if (null != $what) {

            if (!isset($this->container[$what])) {
                $result = $this->load($what, $one_shot);
                $result = $result[$what];
            } else {
                // maybe we can backup current item and load it again in one-shot?
                if ($one_shot) {
                    $container = $this->container; // backup

                    $this->container = array(); // cleanup

                    $result = $this->load($what, $one_shot); // load it
                    $result = $result[$what];

                    $this->container = $container; // restore original
                } else {
                    $result = $this->container[$what];
                }
            }
        } else {
            $result = $this->container;
        }

        return $result;
    }

    public function has($what)
    {
        if (isset($this->container[$what])) {
            return true;
        }

        $container_file = $this->source . DIRECTORY_SEPARATOR . $what . '.yaml';

        return ($container_file && file_exists($container_file));
    }

    public function remove($what = null)
    {
        if ($what !== null) {
            unset($this->container[$what]);
        } else {
            $this->container = array();
        }
    }
}