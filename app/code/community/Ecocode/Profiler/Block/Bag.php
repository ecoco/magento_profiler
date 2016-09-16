<?php

/**
 * Class Ecocode_Profiler_Block_Bag
 *
 * @method getBag
 */
class Ecocode_Profiler_Block_Bag
 extends Mage_Core_Block_Template
{
    public function _construct()
    {
        $this->setTemplate('ecocode_profiler/bag.phtml');
        parent::_construct();
    }

    public function getLabels()
    {
        $labels = $this->getData('labels');

        return $labels ? $labels : [];
    }
}
