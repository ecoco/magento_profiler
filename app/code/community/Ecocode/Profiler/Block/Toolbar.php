<?php

class Ecocode_Profiler_Block_Toolbar
    extends Mage_Core_Block_Template
{
    protected $profile    = null;
    protected $collectors = null;

    public function _construct()
    {
        $this->setTemplate('ecocode_profiler/toolbar_js.phtml');
        parent::_construct();
    }

    public function getToken()
    {
        if ($this->getData('token')) {
            return $this->getData('token');
        }

        if ($this->getProfile()) {
            return $this->getProfile()->getToken();
        }

        return false;
    }

    public function getCollectors()
    {
        if ($this->collectors === null) {
            $this->collectors = $this->getProfile()->getCollectors();

        }

        return $this->collectors;
    }

    public function getToolbarItems()
    {
        $blocks = [];

        $layout = $this->getLayout();
        foreach ($this->getCollectors() as $collector) {
            /** @var Ecocode_Profiler_Model_Collector_DataCollectorInterface $collector */
            if (!$block = $layout->getBlock($collector->getBlockToolbarName())) {
                continue;
            }
            $block->setCollector($collector);
            $block->setData('token', $this->getToken());
            $blocks[$collector->getName()] = $block;
        }

        return $blocks;
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
}
