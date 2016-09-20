<?php

class Ecocode_Profiler_Model_Collector_CacheDataCollector
    extends Ecocode_Profiler_Model_Collector_AbstractDataCollector
    implements Ecocode_Profiler_Model_Collector_LateDataCollectorInterface
{
    /**
     * {@inheritdoc}
     */
    public function collect(Mage_Core_Controller_Request_Http $request, Mage_Core_Controller_Response_Http $response, \Exception $exception = null)
    {
        $caches        = [];
        $cacheInstance = Mage::app()->getCacheInstance();

        foreach ($cacheInstance->getTypes() as $cache) {
            $caches[] = $cache->getData();
        }

        /** @var Zend_Cache_Core $cache */
        $cache   = Mage::app()->getCache();
        $backend = $cache->getBackend();

        $backendOptionsProperty = new ReflectionProperty('Zend_Cache_Backend', '_options');
        $backendOptionsProperty->setAccessible(true);

        $this->data = [
            'backend_name'           => get_class($backend),
            'backend_options'   => $backendOptionsProperty->getValue($backend),
            'cache_list'        => $caches,
            'cache_calls'       => [],
            'stats' => [
                'total' => 0,
                'hit'  => 0,
                'miss' => 0,
                'save' => 0,
            ]
        ];
    }

    public function lateCollect()
    {
        $this->collectCacheCallData();

    }

    protected function collectCacheCallData()
    {
        $cache = Mage::app()->getCacheInstance();
        if (!$cache instanceof Ecocode_Profiler_Model_Core_Cache) {
            return;
        }
        $cacheCalls = $cache->getLog();
        $totalTime = 0;
        $stats = [
            'total' => count($cacheCalls),
            'hit'  => 0,
            'miss' => 0,
            'save' => 0,
        ];

        foreach ($cacheCalls as $log) {
            $totalTime += $log['time'];
            switch($log['action']) {
                case 'load':
                    $stats[$log['hit'] ? 'hit' : 'miss']++;
                    break;
                case 'save':
                    $stats['save']++;
                    break;
                default:
                    break;
            }
        }
        $this->data['stats'] = $stats;
        $this->data['total_time'] = $totalTime;
        $this->data['cache_calls'] = $cache->getLog();
    }

    public function getBackendName()
    {
        return $this->data['backend_name'];
    }

    public function getBackendOptions()
    {
        return $this->data['backend_options'];
    }

    public function getStats($key = null)
    {
        if ($key) {
            return $this->data['stats'][$key];
        }
        return $this->data['stats'];
    }

    public function getTotalTime()
    {
        return $this->data['total_time'];
    }


    public function getCacheList()
    {
        return $this->data['cache_list'];
    }


    public function getCacheCalls()
    {
        return $this->data['cache_calls'];
    }

    public function getName()
    {
        return 'cache';
    }
}