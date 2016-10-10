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

    public function setCollector(Ecocode_Profiler_Model_Collector_DataCollectorInterface $collector)
    {
        $this->collector = $collector;
    }

    public function getCollector()
    {
        return $this->collector;
    }

    /**
     * @return Ecocode_Profiler_Model_Profile
     */
    public function getProfile()
    {
        if ($this->profile === null) {
            $this->profile = Mage::registry('current_profile');
        }
        return $this->profile;
    }

    public function renderBag($bag, array $data = [])
    {
        /** @var Ecocode_Profiler_Block_Bag $bagBlock */
        $bagBlock =  $this->getLayout()->createBlock('ecocode_profiler/bag');
        $data['bag'] = $bag;
        $bagBlock->setData($data);

        return $bagBlock->toHtml();
    }
}
