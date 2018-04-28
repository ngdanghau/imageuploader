<?php

class ChipVN_Cache_Adapter_Memcached extends ChipVN_Cache_Adapter_Abstract
{
    /**
     * Memcache instance.
     *
     * @var Memcache
     */
    protected $cache;

    /**
     * Cache options.
     *
     * @var array
     */
    protected $options = array(
        'servers' => array(
            array(
                'host'   => '127.0.0.1',
                'port'   => 11211,
                'weight' => 0,
            ),
        )
    );

    /**
     * Determine if the key is exist or not.
     *
     * @param string $key
     *
     * @return boolean
     */
    public function has($key)
    {
        $key = $this->sanitize($key);
        $this->getCache()->get($key);

        return $this->getCache()->getResultCode() !== Memcached::RES_NOTFOUND;
    }

    /**
     * Set a cache entry.
     *
     * @param strign       $key
     * @param mixed        $value
     * @param null|integer $expires In seconds
     *
     * @return boolean
     */
    public function set($key, $value, $expires = null)
    {
        $key     = $this->sanitize($key);
        $expires = $expires ? $expires : $this->options['expires'];

        return $this->getCache()->set($key, $value, $expires);
    }

    /**
     * Get a cache entry.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->has($key)
            ? $this->getCache()->get($this->sanitize($key))
            : $default;
    }

    /**
     * Delete a cache entry.
     *
     * @param string $name
     *
     * @return boolean
     */
    public function delete($key)
    {
        $key = $this->sanitize($key);

        return $this->getCache()->delete($key);
    }

    /**
     * Delete all cache entries.
     *
     * @return boolean
     */
    public function flush()
    {
        $this->getCache()->flush();
    }

    /**
     * Run garbage collect.
     */
    public function garbageCollect()
    {
    }

    /**
     * Gets cache instance.
     *
     * @return Memcache
     */
    protected function getCache()
    {
        if (!isset($this->cache)) {
            $this->cache = new Memcached();
            foreach ($this->options['servers'] as $server) {
                $this->cache->addServer($server['host'], $server['port'], $server['weight']);
            }
        }

        return $this->cache;
    }
}
