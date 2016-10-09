<?php

/**
 * Class Ecocode_Profiler_Model_Collector_ContextDataCollector
 */
class Ecocode_Profiler_Model_Collector_ContextDataCollector
    extends Ecocode_Profiler_Model_Collector_AbstractDataCollector
    implements Ecocode_Profiler_Model_Collector_LateDataCollectorInterface
{

    /**
     * {@inheritdoc}
     */
    public function collect(Mage_Core_Controller_Request_Http $request, Mage_Core_Controller_Response_Http $response, \Exception $exception = null)
    {
        $this->data = ['list' => []];

        return $this;
    }

    public function lateCollect()
    {
        $this->data = ['list' => $this->getContextHelper()->getList()];
    }

    public function getById($id)
    {
        if (isset($this->data['list'][$id])) {
            return $this->data['list'][$id];
        }

        return null;
    }

    public function getList()
    {
        return $this->getData('list', []);
    }

    public function getName()
    {
        return 'context';
    }

}
