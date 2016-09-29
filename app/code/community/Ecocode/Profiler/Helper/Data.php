<?php

/**
 * Class Ecocode_Profiler_Helper_Data
 *
 */
class Ecocode_Profiler_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $backTraceRenderer;

    protected $configClassNameReflection;
    protected $classNameCache;

    /**
     * use the config class cache to retrieve the initial class group
     *
     * @param $className
     * @return string
     */
    public function getClassGroup($className)
    {
        $classNames = $this->getClassNames();
        if (!isset($classNames[$className])) {
            $classNames = $this->getClassNames(true);
            if (!isset($classNames[$className])) {
                return '';
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

    public function renderBackTrace($id, $trace)
    {
        return $this->getBackTraceRenderer()
            ->setData(['id' => $id, 'trace' => $trace])
            ->toHtml();
    }

    /**
     * @return Ecocode_Profiler_Block_BackTrace
     */
    public function getBackTraceRenderer()
    {
        if ($this->backTraceRenderer === null) {
            $this->backTraceRenderer = Mage::app()->getLayout()->createBlock('ecocode_profiler/renderer_backTrace');
        }
        return $this->backTraceRenderer;
    }

}