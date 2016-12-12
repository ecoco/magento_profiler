<?php

/**
 * Class Ecocode_Profiler_Block_Renderer_Context
 *
 * @method getBag
 */
class Ecocode_Profiler_Block_Renderer_Settings_Row
    extends Ecocode_Profiler_Block_Renderer_AbstractRenderer
{
    public function _construct()
    {
        $this->setTemplate('ecocode_profiler/renderer/settings/row.phtml');
        parent::_construct();
    }
}
