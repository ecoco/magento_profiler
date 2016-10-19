<?php

/**
 * Class Ecocode_Profiler_Block_Renderer_Table
 *
 */
class Ecocode_Profiler_Block_Renderer_Table
    extends Ecocode_Profiler_Block_Renderer_AbstractRenderer
{
    public function _construct()
    {
        $this->setTemplate('ecocode_profiler/renderer/table.phtml');
        parent::_construct();
    }

    public function getClass()
    {
        $class = $this->getData('class');

        return $class ? $class : '';
    }

    public function getLabels()
    {
        $labels = $this->getData('labels');

        return $labels ? $labels : [];
    }


    public function getItems()
    {
        $items = $this->getData('items');

        return $items ? $items : [];
    }
}
