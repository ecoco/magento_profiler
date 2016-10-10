<?php

/**
 * Class Ecocode_Profiler_Block_Profiler_Sidebar_Menu
 */
class Ecocode_Profiler_Block_Profiler_Sidebar_Menu extends Mage_Core_Block_Template
{
    protected $profile = null;

    public function getMenuBlocks()
    {
        if (!$this->getProfile()) {
            return [];
        }

        $blocks = [];

        $collectors = $this->getProfile()->getCollectors();
        foreach ($this->getSortedChildBlocks() as $name => $block) {
            if (!$block instanceof Ecocode_Profiler_Block_Collector_Base) {
                continue;
            }

            if(!isset($collectors[$name])) {
                continue;
            }
            $block->setCollector($collectors[$name]);
            $blocks[$collectors[$name]->getName()] = $block;
        }

        return $blocks;
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
}
