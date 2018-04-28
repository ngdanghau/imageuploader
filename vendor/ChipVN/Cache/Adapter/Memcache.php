<?php

class ChipVN_Cache_Adapter_Memcache extends ChipVN_Cache_Adapter_Abstract
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
                'host' => '127.0.0.1',
                'port' => 11211,
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

        return $this->getCache()->get($key) !== false;
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

        if (is_bool($value)) {
            $this->getCache()->set($key.'#REAL', (int) $value, 0, $expires);
        }

        return $this->getCache()->set($key, $value, 0, $expires);
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
        if ($this->has($key)) {
            $key   = $this->sanitize($key);
            $value = $this->getCache()->get($key);

            if (($value === '' || $value === '1')
                && false !== $realValue = $this->getCache()->get($key.'#REAL')
            ) {
                return (bool) $realValue;
            }

            return $value;
        }

        return $default;
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
            $this->cache = new Memcache();
            foreach ($this->options['servers'] as $server) {
                $this->cache->addServer($server['host'], $server['port']);
            }
        }

        return $this->cache;
    }
}
