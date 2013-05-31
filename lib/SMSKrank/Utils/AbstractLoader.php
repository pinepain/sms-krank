<?php

namespace SMSKrank\Utils;

use Symfony\Component\Yaml\Yaml;
use SMSKrank\Utils\Exceptions\LoaderException;

abstract class AbstractLoader
{
    private $source;
    private $container = array();

    public function __construct($source)
    {
        if (!file_exists($source)) {
            throw new LoaderException('Source does not exists');
        }

        $this->source = $source;
    }

    public function load($what = null)
    {
        if (null === $what) {

            if (is_file($this->source)) {

                $container_file = $this->source;

                $_parts = pathinfo($container_file);
                $what   = $_parts['filename']; // since PHP 5.2.0

            } else {

                $dir_content = array_filter(
                    scandir($this->source),
                    function ($val) {
                        return $val[0] != '.' && substr($val, -5) == '.yaml';
                    }
                );

                foreach ($dir_content as $what) {
                    $this->load(substr($what, 0, -5));
                }

                return $this->container;
            }

        } else {
            if (is_file($this->source)) {
                throw new LoaderException('Source directory is file');
            }

            $container_file = $this->source . DIRECTORY_SEPARATOR . $what . '.yaml';
        }

        $container_file = realpath($container_file);

        if (!file_exists($container_file)) {
            throw new LoaderException("Container file '{$what}' does not exists");
        }

        if (!is_readable($container_file)) {
            throw new LoaderException("Container file '{$what}' is not readable");
        }

        $loaded = Yaml::parse(file_get_contents($container_file));

        if (!is_array($loaded)) {
            throw new LoaderException("Garbage in container file '{$what}' ({$container_file})");
        }

        $parsed = $this->postLoad($loaded, $what);

        if (!is_array($parsed)) {
            throw new LoaderException("Garbage data after parsing in '{$what}' container");
        }

        $this->container = array_merge($this->container, $parsed);

        ksort($this->container);

        return $this->container;
    }

    public function get($what = null)
    {
        if (!isset($this->container[$what])) {
            $this->load($what);
        }

        return $this->container[$what];
    }

    public function has($what)
    {
        try {
            $this->get($what);
            return true;
        } catch (\Exception $e) {
        }

        return false;
    }

    public function unload($what = null)
    {
        if ($what !== null) {
            unset($this->container[$what]);
        } else {
            $this->container = array();
        }
    }


    /**
     * Post-load hook, process or validate loaded data
     *
     * @param array  $loaded
     * @param string $what
     *
     * @return array
     */
    protected function postLoad(array $loaded, $what)
    {
        return $loaded;
    }
}