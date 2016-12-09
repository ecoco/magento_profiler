<?php

/**
 * Class Ecocode_Profiler_Block_Collector_Base
 */
class Ecocode_Profiler_Block_Collector_Base
    extends Mage_Core_Block_Template
{
    /** @var Ecocode_Profiler_Model_Collector_DataCollectorInterface */
    protected $collector;

    /** @var Ecocode_Profiler_Model_Profile */
    protected $profile;

    /** @var Ecocode_Profiler_Helper_Renderer */
    protected $rendererHelper;

    /**
     * @codeCoverageIgnore
     * @param Ecocode_Profiler_Model_Collector_DataCollectorInterface $collector
     */
    public function setCollector(Ecocode_Profiler_Model_Collector_DataCollectorInterface $collector)
    {
        $this->collector = $collector;
    }

    /**
     * @codeCoverageIgnore
     * @return Ecocode_Profiler_Model_Collector_DataCollectorInterface
     */
    public function getCollector()
    {
        return $this->collector;
    }

    /**
     * @codeCoverageIgnore
     * @return Ecocode_Profiler_Model_Profile
     */
    public function getProfile()
    {
        if ($this->profile === null) {
            $this->profile = Mage::registry('current_profile');
        }
        return $this->profile;
    }

    /**
     * @codeCoverageIgnore
     * @return Ecocode_Profiler_Helper_Renderer
     */
    public function getRendererHelper()
    {
        if ($this->rendererHelper === null) {
            $this->rendererHelper = Mage::helper('ecocode_profiler/renderer');
        }

        return $this->rendererHelper;
    }

    public function getToken()
    {
        return $this->getProfile()->getToken();
    }

}
