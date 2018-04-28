<?php

abstract class ChipVN_Cache_Adapter_Abstract
{
    /**
     * Default value expires
     */
    const DEFAULT_EXPIRES = 900; // seconds

    /**
     * Cache options.
     *
     * @var array
     */
    protected $defaultOptions = array(
        'prefix'  => '',
        'expires' => self::DEFAULT_EXPIRES,
    );

    /**
     * Create a storage instance.
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->setOptions($options);
    }

    /**
     * Cache options.
     *
     * @var array
     */
    protected $options = array();

    /**
     * Sanitize cache key.
     *
     * @param  string $key
     * @return string
     */
    protected function sanitize($key)
    {
        return $this->options['prefix'].md5($key);
    }

    /**
     * Set cache options.
     *
     * @param  array $options
     * @return array
     */
    public function setOptions(array $options)
    {
        return $this->options = array_merge($this->defaultOptions, $this->options, $options);
    }

    /**
     * Set cache option by name, value.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * Get cache options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Put a cache entry.
     *
     * @param  string       $key
     * @param  string       $value
     * @param  null|integer $expires In seconds
     * @return boolean
     */
    public function put($key, $value, $expires = null)
    {
        return $this->set($key, $value, $expires);
    }

    /**
     * Determine if the key is exist or not.
     *
     * @param  string  $key
     * @return boolean
     */
    abstract public function has($key);

    /**
     * Set a cache entry.
     *
     * @param  strign       $key
     * @param  mixed        $value
     * @param  null|integer $expires In seconds
     * @return boolean
     */
    abstract public function set($key, $value, $expires = null);

    /**
     * Get a cache entry.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    abstract public function get($key, $default = null);

    /**
     * Delete a cache entry.
     *
     * @param  string  $name
     * @return boolean
     */
    abstract public function delete($key);

    /**
     * Delete all cache entries.
     *
     * @return boolean
     */
    abstract public function flush();

    /**
     * Run garbage collect.
     *
     * @return void
     */
    abstract public function garbageCollect();
}
