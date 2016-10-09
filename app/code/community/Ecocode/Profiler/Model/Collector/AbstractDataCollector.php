<?php

/**
 * Class Ecocode_Profiler_Model_Collector_AbstractDataCollector
 */
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
     * @param      $key
     * @param null $default
     * @return mixed
     */
    protected function getData($key, $default = null)
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
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

    protected function getBacktrace($options = DEBUG_BACKTRACE_PROVIDE_OBJECT)
    {
        if (!function_exists('debug_backtrace')) {
            return false;
        }

        return debug_backtrace($options);
    }
}
