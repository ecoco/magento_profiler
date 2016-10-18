<?php


class Ecocode_Profiler_Autoloader
{
    public static $autoloader;

    /** @var Closure */
    protected $includeFile;
    protected $classMap = [];

    /**
     * @codeCoverageIgnore
     * @return Ecocode_Profiler_Autoloader
     */
    public static function getAutoloader()
    {
        if (self::$autoloader === null) {
            self::$autoloader = new Ecocode_Profiler_Autoloader();
        }

        return self::$autoloader;
    }

    /**
     * Registers this instance as an autoloader.
     * @param bool $prepend Whether to prepend the autoloader or not
     * @codeCoverageIgnore
     */
    public function register($prepend = false)
    {
        spl_autoload_register([$this, 'loadClass'], true, $prepend);
    }

    /**
     * Unregisters this instance as an autoloader.
     * @codeCoverageIgnore
     */
    public function unregister()
    {
        spl_autoload_unregister([$this, 'loadClass']);
    }

    public function loadClass($class)
    {
        if (isset($this->classMap[$class])) {
            /**
             * Scope isolated include.
             *
             * Prevents access to $this/self from included files.
             *
             * @param $file
             */
            $includeFile = function ($file) {
                include $file;
            };
            $includeFile($this->classMap[$class]);

            return true;
        }

        return false;
    }

    public function addOverwrite($className, $file)
    {
        if (strpos($file, '/') !== 0) {
            $overwriteDir = __DIR__ . DIRECTORY_SEPARATOR . 'overwrite' . DIRECTORY_SEPARATOR;
            $file         = $overwriteDir . $file;
        }

        $this->addToClassMap($className, $file);


        return $this;
    }

    public function addToClassMap($className, $file)
    {
        $this->classMap[$className] = $file;

        return $this;
    }

}
