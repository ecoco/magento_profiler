<?php


class Ecocode_Profiler_Autoloader
{
    public static $autoloader;

    protected $classMap = [];


    public static function getAutoloader()
    {
        if (self::$autoloader === null) {
            self::$autoloader = new Ecocode_Profiler_Autoloader();
        }
        return self::$autoloader;
    }

    /**
     * Registers this instance as an autoloader.
     *
     * @param bool $prepend Whether to prepend the autoloader or not
     */
    public function register($prepend = false)
    {
        spl_autoload_register(array($this, 'loadClass'), true, $prepend);
    }

    /**
     * Unregisters this instance as an autoloader.
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }

    public function loadClass($class)
    {
        if (isset($this->classMap[$class])) {
            ecocodeAutoloaderIncludeFile($this->classMap[$class]);

            return true;
        }
    }

    public function addOverwrite($className, $file)
    {
        if (strpos($file, '/') !== 0) {
            $overwriteDir = __DIR__ . DIRECTORY_SEPARATOR . 'overwrite' . DIRECTORY_SEPARATOR;
            $file = $overwriteDir . $file;
        }
        $this->classMap[$className] = $file;

        return $this;
    }
}


/**
 * Scope isolated include.
 *
 * Prevents access to $this/self from included files.
 */
function ecocodeAutoloaderIncludeFile($file)
{
    include $file;
}
