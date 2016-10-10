<?php

/**
 * Class Ecocode_Profiler_Block_Profiler_Sidebar
 */
class Ecocode_Profiler_Block_Profiler_Sidebar
    extends Mage_Core_Block_Template
{
    protected $profile = null;

    public function _construct()
    {
        $this->setTemplate('ecocode_profiler/layout/sidebar.phtml');
        parent::_construct();
    }

    public function getCollector()
    {
        return $this->getProfile()->getCollector(Mage::registry('current_panel'));
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
