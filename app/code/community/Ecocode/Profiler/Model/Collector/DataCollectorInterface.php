<?php

/**
 * Interface Ecocode_Profiler_Model_Collector_DataCollectorInterface
 */
interface Ecocode_Profiler_Model_Collector_DataCollectorInterface
{
    public function init();

    /**
     * @param Mage_Core_Controller_Request_Http  $request
     * @param Mage_Core_Controller_Response_Http $response
     * @param Exception|null                     $exception
     * @return mixed
     */
    public function collect(Mage_Core_Controller_Request_Http $request, Mage_Core_Controller_Response_Http $response, \Exception $exception = null);

    /**
     * Returns the name of the collector.
     *
     * @return string The collector name
     */
    public function getName();
}
