<?php

/**
 * Class Ecocode_Profiler_Block_Bag
 *
 * @method getBag
 */
class Ecocode_Profiler_Block_Renderer_Bag
 extends Ecocode_Profiler_Block_Renderer_AbstractRenderer
{
    public function _construct()
    {
        $this->setTemplate('ecocode_profiler/renderer/bag.phtml');
        parent::_construct();
    }

    public function getLabels()
    {
        $labels = $this->getData('labels');

        return $labels ? $labels : [];
    }
}
