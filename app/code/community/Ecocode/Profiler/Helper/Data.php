<?php

/**
 * Class Ecocode_Profiler_Helper_Data
 */
class Ecocode_Profiler_Helper_Data
{
    protected static $version;
    protected static $overwriteDirectory;

    protected $configClassReflection;
    protected $classNameCache;
    protected $profilerSession;

    public static function getOverwriteDir()
    {
        if (self::$overwriteDirectory === null) {
            self::$overwriteDirectory = join(DIRECTORY_SEPARATOR, [MAGENTO_ROOT, 'var', 'cache', '']);
        }

        if (!file_exists(self::$overwriteDirectory)) {
            mkdir(self::$overwriteDirectory, 0775, true);
        }

        return self::$overwriteDirectory;
    }

    /**
     * @return string
     */
    public static function getVersion()
    {
        if (self::$version === null && class_exists('Mage') && $config = Mage::getConfig()) {
            //try to load from magento
            $config = $config->getModuleConfig('Ecocode_Profiler');
            if ($config && $config->version) {
                self::$version = (string)$config->version;
            }
        }

        if (self::$version === null) {
            //try to load it from the config directly
            $configFile = join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'etc', 'config.xml']);
            $xml        = file_get_contents($configFile);
            preg_match('/<version>([0-9\.]+)<\/version>/', $xml, $matches);
            if (!$matches) {
                throw new RuntimeException('unable to determinate profiler version');
            }
            self::$version = $matches[1];
        }

        return self::$version;
    }

    /**
     * @param string $fileName
     * @param string $className
     */
    public static function loadRenamedClass($fileName, $className)
    {

        $ds         = DIRECTORY_SEPARATOR;
        $sourceFile = MAGENTO_ROOT . $ds . 'app' . $ds . 'code' . $ds . $fileName;
        $sourceMd5  = md5_file($sourceFile);

        $fileName  = sprintf('%s-%s-%s.php', $className, self::getVersion(), $sourceMd5);
        $cacheFile = self::getOverwriteDir() . $fileName;

        if (!file_exists($cacheFile)) {
            $code = file_get_contents($sourceFile);
            $code = preg_replace('/class ([^\s]+)/', 'class ' . $className, $code);

            file_put_contents($cacheFile, $code);
        }

        //register overwrite
        Ecocode_Profiler_Autoloader::getAutoloader()
            ->addToClassMap($className, $cacheFile);
    }

    /**
     * use the config class cache to retrieve the initial class group
     *
     * @param string|object $className
     * @return string
     */
    public function resolveClassGroup($className)
    {
        if (is_object($className)) {
            $className = get_class($className);
        }

        $classNames = $this->getClassNames();
        if (!isset($classNames[$className])) {
            $classNames = $this->getClassNames(true);
            if (!isset($classNames[$className])) {
                return 'unknown';
            }
        }

        return $classNames[$className];
    }

    protected function getClassNames($reload = false)
    {
        if ($this->classNameCache !== null && $reload === false) {
            return $this->classNameCache;
        }
        if ($this->configClassReflection === null) {
            $this->configClassReflection = new ReflectionProperty('Mage_Core_Model_Config', '_classNameCache');
            $this->configClassReflection->setAccessible(true);
        }

        $classNameCache = $this->configClassReflection->getValue(Mage::getConfig());
        foreach ($classNameCache as $groupRoot) {
            foreach ($groupRoot as $module => $classNames) {
                foreach ($classNames as $class => $className) {
                    $this->classNameCache[$className] = $module . '/' . $class;
                }
            }
        }

        return $this->classNameCache;
    }

    /**
     * @param string                                                  $token $token
     * @param Ecocode_Profiler_Model_Collector_DataCollectorInterface $collector
     * @return string
     */
    public function getCollectorUrl($token, Ecocode_Profiler_Model_Collector_DataCollectorInterface $collector)
    {
        return $this->getUrl($token, $collector->getName());
    }

    public function getUrl($token = null, $panel = null)
    {
        $params = [];
        if ($token) {
            $params[Ecocode_Profiler_Model_Profiler::URL_TOKEN_PARAMETER] = $token;
        }
        if ($panel) {
            $params['panel'] = $panel;
        }

        return Mage::getUrl('_profiler/index/panel', $params);
    }

    /**
     * removes all backtrace items until
     * one does not match the rules defined
     *
     * @param array $backtrace
     * @param array $ignoreCalls
     * @param array $ignoreInstanceOf
     * @return array
     */
    public function cleanBacktrace(array $backtrace, array $ignoreCalls = [], array $ignoreInstanceOf = [])
    {
        $item = reset($backtrace);
        while ($item && $this->shouldRemoveBacktraceItem($item, $ignoreCalls, $ignoreInstanceOf)) {
            array_shift($backtrace);
            $item = reset($backtrace);
        }

        return $backtrace;
    }

    protected function shouldRemoveBacktraceItem(array $data, array $ignoreCalls = [], array $ignoreInstanceOf = [])
    {
        //remove if not called from a class
        if (!isset($data['class'], $data['function'])) {
            return true;
        }

        $functionIdent = $data['class'] . '::' . $data['function'];
        if (in_array($functionIdent, $ignoreCalls)) {
            return true;
        }

        if (!isset($data['object'])) {
            return false;
        }

        foreach ($ignoreInstanceOf as $instance) {
            if ($data['object'] instanceof $instance) {
                return true;
            }
        }

        return false;
    }

    public function getTokenFromResponse(Mage_Core_Controller_Response_Http $response)
    {
        $token = null;
        foreach ($response->getHeaders() as $header) {
            if ($header['name'] === 'X-Debug-Token') {
                $token = $header['value'];
                break;
            }
        }
        return $token;
    }

    /**
     * @codeCoverageIgnore
     * @return Ecocode_Profiler_Model_Session
     */
    public function getSession()
    {
        if ($this->profilerSession === null) {
            $this->profilerSession = Mage::getSingleton('ecocode_profiler/session');
        }

        return $this->profilerSession;
    }
}
