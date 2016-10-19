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

    public function setCollector(Ecocode_Profiler_Model_Collector_DataCollectorInterface $collector)
    {
        $this->collector = $collector;
    }

    public function getCollector()
    {
        return $this->collector;
    }

    /**
     * @deprecated
     *
     * @param       $bag
     * @param array $data
     * @return mixed
     */
    public function renderBag($bag, array $data = [])
    {
        return $this->getRendererHelper()
            ->renderBag($bag, $data);
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

}
