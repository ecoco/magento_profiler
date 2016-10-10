<?php

/**
 * Class Ecocode_Profiler_Block_Collector_Menu
 */
class Ecocode_Profiler_Block_Collector_Menu extends
    Ecocode_Profiler_Block_Collector_Base
{
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('ecocode_profiler/collector/base/menu.phtml');
    }
}
