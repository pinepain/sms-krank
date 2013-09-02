<?php

namespace SMSKrank\Loaders;

interface LoaderInterface
{
    /**
     * Load containers
     *
     * @param string | null $container Container name to load. By default all available containers will be loaded.
     * @param bool          $one_shot  Do not store loaded containers, just return them
     *
     * @return array
     * @throws Exceptions\LoaderException
     */
    public function load($container = null, $one_shot = false);

    /**
     * Get containers. If they was not loaded - load and get
     *
     * @param string | null $container Container name to load. By default all available containers will be loaded
     * @param bool          $one_shot  Do not store loaded containers, just return them (not applicable for already loaded)
     *
     * @return mixed
     */
    public function get($container = null, $one_shot = false);

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