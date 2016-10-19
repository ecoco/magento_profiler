<?php

/**
 * Class Ecocode_Profiler_Model_Profiler
 */
class Ecocode_Profiler_Model_Profiler
{
    protected $storage;

    protected $enabled        = false;
    protected $initialized    = false;
    protected $dataCollectors = null;

    const URL_TOKEN_PARAMETER = 'eco_token';

    public function __construct($storage = null)
    {
        if ($this->storage) {
            $this->storage = $storage;
        }
    }

    public function init()
    {
        $this->enable();
        if (!$this->initialized) {
            $this->initialized = true;

            foreach ($this->getDataCollectors() as $dataCollector) {
                /** @var Ecocode_Profiler_Model_Collector_DataCollectorInterface $dataCollector */
                $dataCollector->init();
            }
        }
    }


    public function enable()
    {
        $this->enabled = true;
    }

    public function disable()
    {
        $this->enabled = false;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function getDataCollector($name)
    {
        $collectors = $this->getDataCollectors();

        return isset($collectors[$name]) ? $collectors[$name] : false;
    }

    public function getDataCollectors()
    {
        if ($this->dataCollectors === null) {
            $contextCollector     = Mage::getSingleton('ecocode_profiler/collector_contextDataCollector');
            $this->dataCollectors = [];

            $this->dataCollectors[$contextCollector->getName()] = $contextCollector;

            $collectors = Mage::getConfig()->getNode('ecocode/profiler/collectors')->asArray();
            foreach ($collectors as $classGroup) {
                $collector = Mage::getSingleton($classGroup);
                if (!$collector instanceof Ecocode_Profiler_Model_Collector_DataCollectorInterface) {
                    throw new InvalidArgumentException('collector must implement "Ecocode_Profiler_Model_Collector_DataCollectorInterface"');
                }
                $this->dataCollectors[$collector->getName()] = $collector;
            }
        }

        return $this->dataCollectors;
    }

    /**
     * @param Mage_Core_Controller_Request_Http  $request
     * @param Mage_Core_Controller_Response_Http $response
     * @return Ecocode_Profiler_Model_Profile|null
     */
    public function collect(Mage_Core_Controller_Request_Http $request, Mage_Core_Controller_Response_Http $response)
    {
        if (false === $this->isEnabled()) {
            return;
        }

        $start   = microtime(true);
        $profile = new Ecocode_Profiler_Model_Profile(substr(hash('sha256', uniqid(mt_rand(), true)), 0, 6));
        $profile->setTime(time());
        $profile->setUrl($request->getRequestString() ? $request->getRequestString() : '/');
        $profile->setMethod($request->getMethod());
        $profile->setStatusCode($response->getHttpResponseCode());
        $profile->setIp($request->getClientIp());

        $response->setHeader('X-Debug-Token', $profile->getToken());

        foreach ($this->getDataCollectors() as $collector) {
            /** @var Ecocode_Profiler_Model_Collector_DataCollectorInterface $collector */
            $collector->collect($request, $response);

            $profile->addCollector($collector);
        }
        $collectTime = microtime(true) - $start;
        $profile->setCollectTime($collectTime);

        return $profile;
    }

    /**
     * Loads the Profile for the given token.
     *
     * @param string $token A token
     *
     * @return Ecocode_Profiler_Model_Profile A Profile instance
     */
    public function loadProfile($token)
    {
        return $this->getStorage()->read($token);
    }


    public function find($ip, $url, $limit, $method, $start, $end)
    {
        return $this->getStorage()->find($ip, $url, $limit, $method, $this->getTimestamp($start), $this->getTimestamp($end));
    }

    public function saveProfile(Ecocode_Profiler_Model_Profile $profile)
    {
        if (false === $this->isEnabled()) {
            return;
        }

        // late collect
        foreach ($profile->getCollectors() as $collector) {
            if ($collector instanceof Ecocode_Profiler_Model_Collector_LateDataCollectorInterface) {
                $collector->lateCollect();
            }
        }

        if (!$this->getStorage()->write($profile)) {
            throw new Exception('Unable to store the profiler information.', ['configured_storage' => get_class($this->storage)]);
        }

        return true;
    }

    /**
     * @return Ecocode_Profiler_Model_Profiler_StorageInterface
     */
    protected function getStorage()
    {
        if (!$this->storage) {
            $baseDir       = Mage::getBaseDir('var') . DS . '_profile';
            $this->storage = Mage::getSingleton('ecocode_profiler/profiler_fileStorage', ['dsn' => 'file:' . $baseDir]);
        }

        return $this->storage;
    }

    private function getTimestamp($value)
    {
        if (null === $value || '' == $value) {
            return;
        }

        try {
            $value = new \DateTime(is_numeric($value) ? '@' . $value : $value);
        } catch (\Exception $e) {
            return;
        }

        return $value->getTimestamp();
    }
}
