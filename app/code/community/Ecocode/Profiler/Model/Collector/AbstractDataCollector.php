<?php

abstract class Ecocode_Profiler_Model_Collector_AbstractDataCollector
    implements Ecocode_Profiler_Model_Collector_DataCollectorInterface,
    Serializable
{
    protected $data = [];


    public function init()
    {

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
}