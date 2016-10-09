<?php

/**
 * Class Ecocode_Profiler_Model_Collector_TimeDataCollector
 */
class Ecocode_Profiler_Model_Collector_TimeDataCollector
    extends Ecocode_Profiler_Model_Collector_AbstractDataCollector
    implements Ecocode_Profiler_Model_Collector_LateDataCollectorInterface
{
    /**
     * {@inheritdoc}
     */
    public function collect(Mage_Core_Controller_Request_Http $request, Mage_Core_Controller_Response_Http $response, \Exception $exception = null)
    {
        $this->data = [
            'total_time' => 0
        ];
    }

    public function lateCollect()
    {
        $startTime = Mage::app()->getStartTime();

        if ($startTime) {
            $this->data['total_time'] = microtime(true) - $startTime;
        }

        return $this;
    }

    public function getTotalTime()
    {
        return $this->data['total_time'];
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    public function getName()
    {
        return 'time';
    }
}
