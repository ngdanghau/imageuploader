<?php

/**
 * Note: Life time of cache entries can't longer than session expires
 */
class ChipVN_Cache_Adapter_Array extends ChipVN_Cache_Adapter_Abstract
{
    /**
     * Array of data.
     *
     * @var array
     */
    protected $data = array();

    /**
     * Cache options.
     *
     * @var array
     */
    protected $options = array();

    /**
     * Set a cache entry.
     *
     * @param  strign       $key
     * @param  mixed        $value
     * @param  null|integer $expires In seconds
     * @return boolean
     */
    public function set($key, $value, $expires = null)
    {
        $key     = $this->sanitize($key);
        $expires = $expires ? $expires : $this->options['expires'];

        $this->data[$key] = array(time() + $expires, $value);

        return true;
    }

    /**
     * Determine if the key is exist or not.
     *
     * @param  string  $key
     * @return boolean
     */
    public function has($id)
    {
        $key = $this->sanitize($id);

        return isset($this->data[$key]) && $this->data[$key][0] > time()
            ? true
            : $this->delete($id) && false;
    }

    /**
     * Get a cache entry.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->has($key) ? $this->data[$this->sanitize($key)][1] : $default;
    }

    /**
     * Delete a cache entry.
     *
     * @param  string  $name
     * @return boolean
     */
    public function delete($key)
    {
        $key = $this->sanitize($key);

        unset($this->data[$key]);

        return true;
    }

    /**
     * Delete all cache entries.
     *
     * @return boolean
     */
    public function flush()
    {
        $this->data = array();
    }

    /**
     * Run garbage collect.
     *
     * @return void
     */
    public function garbageCollect()
    {
    }
}
