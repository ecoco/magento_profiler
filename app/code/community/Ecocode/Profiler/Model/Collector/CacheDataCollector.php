<?php

class Ecocode_Profiler_Model_Collector_CacheDataCollector
    extends Ecocode_Profiler_Model_Collector_AbstractDataCollector
{
    /**
     * {@inheritdoc}
     */
    public function collect(Mage_Core_Controller_Request_Http $request, Mage_Core_Controller_Response_Http $response, \Exception $exception = null)
    {
        $caches = [];
        foreach (Mage::app()->getCacheInstance()->getTypes() as $cache) {
            $caches[] = $cache->getData();
        }

        $this->data = [
            'cache_list' => $caches
        ];
    }

    public function getCacheList()
    {
        return $this->data['cache_list'];
    }

    public function getName()
    {
        return 'cache';
    }
}