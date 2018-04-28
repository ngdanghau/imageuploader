<?php
/**
 * Helper class to autoload ChipVN package.
 *
 * $loader = ChipVN_ClassLoader_Loader::getInstance();
 * $loader = new ChipVN_ClassLoader_Loader; // is works too
 * $loader->register('ChipVN', '/path/path/path');
 * $loader->registerAutoload();
 *
 * or
 *
 * ChipVN_ClassLoader_Loader::register('ChipVN', '/path/path/path');
 * ChipVN_ClassLoader_Loader::registerAutoload();
 */

if (version_compare(PHP_VERSION, '5.3', '>=')) {
    class ChipVN_ClassLoader_Base {}
} else {
    class ChipVN_ClassLoader_Base
    {
        public static function register($prefix, $path)
        {
            return ChipVN_ClassLoader_Processor::getInstance()->register($prefix, $path);
        }

        public static function autoLoad($className)
        {
            ChipVN_ClassLoader_Processor::getInstance()->autoLoad($className);
        }

        public static function registerAutoload()
        {
            ChipVN_ClassLoader_Processor::getInstance()->registerAutoload();
        }
    }
}

class ChipVN_ClassLoader_Loader extends ChipVN_ClassLoader_Base
{
    /**
     * @return ChipVN_ClassLoader_Processor
     */
    public static function getInstance()
    {
        return ChipVN_ClassLoader_Processor::getInstance();
    }

    /**
     * Handle dynamic static calling.
     */
    public static function __callStatic($method, $arguments)
    {
        return call_user_func_array(array(self::getInstance(), $method), $arguments);
    }
    /**
     * Handle dynamic calling.
     */
    public function __call($method, $arguments)
    {
        return self::__callStatic($method, $arguments);
    }
}

class ChipVN_ClassLoader_Processor
{
    /**
     * Class instance.
     *
     * @var ChipVN_ClassLoader_Loader
     */
    protected static $instance;

    /**
     * Array of prefixes, namespaces.
     *
     * @var array
     */
    protected $prefixes = array();

    /**
     * Create new instance.
     */
    protected function __construct()
    {
        $this->register('ChipVN', dirname(dirname(dirname(__FILE__))));
    }

    /**
     * Gets ChipVN_ClassLoader_Loader instance.
     *
     * @return ChipVN_ClassLoader_Loader
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Register prefix, namespace for autoloading.
     *
     * @param  string $prefix
     * @param  string $path
     * @return ChipVN_ClassLoader_Loader
     */
    public function register($prefix, $path)
    {
        if ($path = realpath($path)) {
            $this->prefixes[$prefix] = $path . DIRECTORY_SEPARATOR;
        }

        return $this;
    }

    /**
     * Autoload a class by name.
     *
     * @param  string $className
     * @return void
     */
    public function autoLoad($className)
    {
        $className = trim($className, '\\');
        // psr-4
        $fileName = strtr($className, array(
            '\\' => DIRECTORY_SEPARATOR,
        )) . '.php';

        // psr-0
        $filePsr0 = strtr($fileName, array(
            '_' => DIRECTORY_SEPARATOR,
        ));

        foreach ($this->prefixes as $prefix => $path) {
            if (strpos($className, $prefix) === 0) {
                if (file_exists($file = $path . $fileName) || file_exists($file = $path . $filePsr0)) {
                    include_once $file;
                }
            }
        }
    }

    /**
     * Register autoloading
     *
     * @return ChipVN_ClassLoader_Loader
     */
    public function registerAutoload()
    {
        spl_autoload_register(array($this, 'autoLoad'));
    }
}