<?php

/**
 * Class Ecocode_Profiler_Helper_Data
 *
 */
class Ecocode_Profiler_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $configClassNameReflection;
    protected $classNameCache;

    /**
     * use the config class cache to retrieve the initial class group
     *
     * @param string|object $className
     * @return string
     */
    public function getClassGroup($className)
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
        if ($this->configClassNameReflection === null) {
            $this->configClassNameReflection = new ReflectionProperty('Mage_Core_Model_Config', '_classNameCache');
            $this->configClassNameReflection->setAccessible(true);
        }

        $classNameCache = $this->configClassNameReflection->getValue(Mage::getConfig());
        foreach($classNameCache as $groupRoot) {
            foreach ($groupRoot as $module => $classNames) {
                foreach($classNames as $class => $className) {
                    $this->classNameCache[$className] = $module . '/' . $class;
                }
            }
        }

        return $this->classNameCache;
    }

    /**
     * @param string $token                                                        $token
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

        return $this->_getUrl('_profiler/index/panel', $params);
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
    public function cleanBacktrace(array $backtrace, array $ignoreCalls = [], array $ignoreInstanceOf = [] )
    {
        $item = reset($backtrace);
        while ($item && $this->_cleanBacktrace($item, $ignoreCalls, $ignoreInstanceOf)) {
            array_shift($backtrace);
            $item= reset($backtrace);
        }

        return $backtrace;
    }

    public function _cleanBacktrace(array $data, array $ignoreCalls = [], array $ignoreInstanceOf = [] )
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

}