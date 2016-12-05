<?php

class Ecocode_Profiler_Model_Config
{
    protected $config;

    protected $userConfig;

    protected $cache = [];

    public function getValue($key, $default = null)
    {
        if (!isset($this->cache[$key])) {
            $keyParts = explode('/', $key);

            $value = $this->getConfig();
            while ($keyParts) {
                $_key = array_shift($keyParts);
                if (!isset($value[$_key])) {
                    $value = $default;
                    break;
                }
                $value = $value[$_key];
            }

            return $this->cache[$key] = $value;
        }

        return $this->cache[$key];
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function saveValue($key, $value)
    {
        $keyParts = explode('/', $key);
        $lastKey  = array_pop($keyParts);

        $node             = $this->getNode($keyParts);
        $node->{$lastKey} = $value;

        $this->save();

        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function deleteValue($key)
    {
        $keyParts = explode('/', $key);
        $lastKey  = array_pop($keyParts);

        $node = $this->getNode($keyParts);

        unset($node->{$lastKey});

        $this->save();

        return $this;
    }

    /**
     * @param Ecocode_Profiler_Model_Collector_DataCollectorInterface $collector
     * @param                                                         $key
     * @param                                                         $value
     * @return Ecocode_Profiler_Model_Config
     */
    public function saveCollectorValue(
        Ecocode_Profiler_Model_Collector_DataCollectorInterface $collector,
        $key, $value)
    {
        $key = 'collector' . '/' . $collector->getName() . '/' . $key;

        return $this->saveValue($key, $value);
    }

    /**
     * @param Ecocode_Profiler_Model_Collector_DataCollectorInterface $collector
     * @param                                                         $key
     * @return Ecocode_Profiler_Model_Config
     */
    public function deleteCollectorValue(
        Ecocode_Profiler_Model_Collector_DataCollectorInterface $collector,
        $key)
    {
        $key = 'collector' . '/' . $collector->getName() . '/' . $key;

        return $this->deleteValue($key);
    }

    public function getNode($keyParts)
    {
        $config = $this->getUserConfig();
        if (!is_array($keyParts)) {
            $keyParts = explode('/', $keyParts);
        }

        while ($keyParts) {
            $key = array_shift($keyParts);
            if (!isset($config->{$key})) {
                $config->{$key} = new stdClass();
            }
            $config = &$config->{$key};
        }

        return $config;
    }

    protected function save()
    {
        $userConfig = $this->getUserConfig();

        file_put_contents(
            $this->getUserConfigFile(),
            json_encode($userConfig, JSON_PRETTY_PRINT)
        );

        $this->reset();

        return $this;
    }

    protected function reset()
    {
        $this->config     = null;
        $this->userConfig = null;
        $this->cache      = [];

        return $this;
    }

    public function getCollectorConfig(
        Ecocode_Profiler_Model_Collector_DataCollectorInterface $collector
    )
    {
        $config = $this->getConfig();
        if (isset($config['collector'][$collector->getName()])) {
            return $config['collector'][$collector->getName()];
        }

        return [];
    }

    /**
     * @param Ecocode_Profiler_Model_Collector_DataCollectorInterface $collector
     * @param                                                         $key
     * @param null                                                    $default
     * @return mixed
     */
    public function getCollectorValue(
        Ecocode_Profiler_Model_Collector_DataCollectorInterface $collector,
        $key,
        $default = null
    )
    {
        $collectorKey = 'collector' . '/' . $collector->getName() . '/' . $key;

        if ($value = $this->getValue($collectorKey)) {
            return $value;
        }

        return $this->getValue($key, $default);
    }

    /**
     * @return array
     */
    protected function getConfig()
    {
        if ($this->config === null) {
            //we cant use the magento config as we need to config before the magento config is available
            $baseConfig = $this->getBaseConfig();
            $userConfig = $this->getUserConfig(true);

            $this->config = array_replace_recursive(
                $baseConfig,
                $userConfig
            );
        }

        return $this->config;
    }

    /**
     * @codeCoverageIgnore
     * @return array
     */
    protected function getBaseConfig()
    {
        $baseConfigFile = $this->getBaseConfigFile();

        return json_decode(file_get_contents($baseConfigFile), true);
    }

    /**
     * @param bool $asArray
     * @return stdClass|array
     */
    protected function getUserConfig($asArray = false)
    {
        if (!$this->userConfig || $asArray) {
            $userConfigFile = $this->getUserConfigFile();

            $userConfig = [];

            if (file_exists($userConfigFile)) {
                $userConfig = json_decode(file_get_contents($userConfigFile), $asArray);
            }

            if ($asArray) {
                return $userConfig;
            }

            $this->userConfig = $userConfig;
        }

        return $this->userConfig;
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    protected function getBaseConfigFile()
    {
        //we cant use getModuleConfig as not yet initialized
        return __DIR__ . DS . '..' . DS . 'etc' . DS . 'config.json';
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    protected function getUserConfigFile()
    {
        return Mage::getBaseDir('var') . DS . '.profiler.conf.json';
    }
}
