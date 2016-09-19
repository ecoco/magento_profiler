<?php

abstract class Ecocode_Profiler_Model_Collector_AbstractDataCollector
    implements Ecocode_Profiler_Model_Collector_DataCollectorInterface,
    Serializable
{
    protected $data = [];

    protected $contextHelper;


    public function init()
    {
        //fill if needed
    }

    public function serialize()
    {
        return serialize($this->data);
    }

    public function unserialize($data)
    {
        $this->data = unserialize($data);
    }

    public function getBlockPanelName()
    {
        return $this->getName() . '_panel';
    }

    public function getBlockMenuName()
    {
        return $this->getName() . '_menu';
    }

    public function getBlockToolbarName()
    {
        return 'profiler.' . $this->getName() . '.toolbar';
    }

    /**
     * @return integer
     */
    public function getContextId()
    {
        return $this->getContextHelper()->getCurrentId();
    }

    /**
     * @return Ecocode_Profiler_Helper_Context
     */
    public function getContextHelper()
    {
        if ($this->contextHelper === null) {
            $this->contextHelper = Mage::helper('ecocode_profiler/context');
        }

        return $this->contextHelper;
    }
}