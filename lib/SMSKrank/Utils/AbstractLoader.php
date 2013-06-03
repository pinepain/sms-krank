<?php

namespace SMSKrank\Utils;

use SMSKrank\Utils\Exceptions\LoaderException;
use Symfony\Component\Yaml\Yaml;

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
                throw new LoaderException("Source directory is file ({$this->source})");
            }

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

        $parsed = $this->postLoad($loaded, $what);

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

    public function get($what = null)
    {
        if (null != $what) {
            if (!isset($this->container[$what])) {
                $this->load($what);
            }

            return $this->container[$what];
        }

        return $this->container;
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

    final protected function getSource()
    {
        return $this->source;
    }
}