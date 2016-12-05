<?php

class Ecocode_Profiler_Block_Profiler_Settings
    extends Mage_Core_Block_Template
{

    public function getCollectorSettingBlocks()
    {
        $blocks = [];

        $layout = $this->getLayout();
        foreach ($this->getCollectors() as $collector) {
            /** @var Ecocode_Profiler_Model_Collector_DataCollectorInterface $collector */

            $blockName = sprintf('profiler.%s.settings', $collector->getName());
            if (!$block = $layout->getBlock($blockName)) {
                continue;
            }
            $block->setCollector($collector);
            $blocks[$collector->getName()] = $block;
        }

        return $blocks;
    }


    public function getCollectors()
    {
        return Mage::getSingleton('ecocode_profiler/profiler')->getDataCollectors();
    }
}
