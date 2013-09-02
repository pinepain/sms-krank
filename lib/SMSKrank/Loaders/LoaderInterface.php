<?php

namespace SMSKrank\Loaders;

interface LoaderInterface
{
    /**
     * Load containers
     *
     * @param null $container Container name to load. By default all available containers will be loaded.
     * @param bool $one_shot  Do not store loaded containers, just return them
     *
     * @return array
     * @throws Exceptions\LoaderException
     */
    public function load($container = null, $one_shot = false);

//    public function get($container = null, $one_shot = false); // TODO: do we need get and load methods? they do quite same thing. I stick to get() and remove load()

    /**
     * Check whether container available
     *
     * @param string $container Container name to check
     *
     * @return bool
     */
    public function has($container);

    /**
     * Remove loaded containers
     *
     * @param null $container Container name to unload. By default all loaded containers will be unloaded.
     *
     * @return mixed
     */
    public function remove($container = null);
}