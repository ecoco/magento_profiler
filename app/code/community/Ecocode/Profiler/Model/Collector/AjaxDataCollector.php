<?php

class Ecocode_Profiler_Model_Collector_AjaxDataCollector
    extends Ecocode_Profiler_Model_Collector_AbstractDataCollector
{
    /**
     * {@inheritdoc}
     */
    public function collect(Mage_Core_Controller_Request_Http $request, Mage_Core_Controller_Response_Http $response, \Exception $exception = null)
    {
        // all collecting is done client side
    }

    public function getName()
    {
        return 'ajax';
    }
}