<?php

class Ecocode_Profiler_Model_Collector_LogDataCollector
    extends Ecocode_Profiler_Model_Collector_AbstractDataCollector
    implements Ecocode_Profiler_Model_Collector_LateDataCollectorInterface
{
    /**
     * {@inheritdoc}
     */
    public function collect(Mage_Core_Controller_Request_Http $request, Mage_Core_Controller_Response_Http $response, \Exception $exception = null)
    {
        $this->data = [
            'entries'     => [],
            'total_count' => 0
        ];
    }


    public function lateCollect()
    {
        $logs = Mage::getLogEntries();

        $this->data = [
            'logs'      => $logs,
            'log_count' => count($logs)
        ];
    }

    public function getLogCount()
    {
        return $this->data['log_count'];
    }

    public function getLogs()
    {
        return $this->data['logs'];
    }

    public function getName()
    {
        return 'log';
    }
}