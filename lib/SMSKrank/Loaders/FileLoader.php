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

        if (is_file($this->source)) {
            throw new LoaderException("Source should be directory");
        }

        $this->source = $source;
        $this->parser = $parser;
    }

    /**
     * @param null $what     Container name to load
     * @param bool $one_shot Do not store result, just return it
     *
     * @return array
     * @throws Exceptions\LoaderException
     */
    public function load($what = null, $one_shot = false)
    {
        if (null === $what) {

            $dir_content = array_filter(
                scandir($this->source),
                function ($val) {
                    return $val[0] != '.' && substr($val, -5) == '.yaml';
                }
            );

            foreach ($dir_content as $what) {
                $this->load(substr($what, 0, -5), $one_shot);
            }

            return $this->container;

        } else {
            $container_file = $this->source . DIRECTORY_SEPARATOR . $what . '.yaml';
        }

        $_container_file = realpath($container_file);

        if (!file_exists($container_file)) {
            throw new LoaderException("Container file '{$what}' does not exists ({$container_file})");
        }

        if (!is_readable($container_file)) {
            throw new LoaderException("Container file '{$what}' is not readable ({$_container_file})");
        }

        $loaded = Yaml::parse(file_get_contents($container_file));

        if (!is_array($loaded)) {
            throw new LoaderException("Garbage in container file '{$what}' ({$_container_file})");
        }

        $container       = $this->container; // backup container
        $parsed          = $this->parser->parse($loaded, $what, $this);
        $this->container = $container; // restore container

        if (!is_array($parsed)) {
            throw new LoaderException("Garbage data after parsing in '{$what}' container");
        }

        if ($one_shot) {
            return $parsed;
        } else {
            $this->container = $parsed + $this->container;
            ksort($this->container);

            return $this->container;
        }
    }

    public function get($what = null, $one_shot = false)
    {
        if (null != $what) {

            if (!isset($this->container[$what])) {
                $result = $this->load($what, $one_shot);
            } else {
                // maybe we can backup current item and load it again in one-shot?
                if ($one_shot) {
                    $container = $this->container; // backup

                    $this->container = array(); // cleanup

                    $result = $this->load($what, $one_shot); // load it

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

        $container_file = realpath($container_file);

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