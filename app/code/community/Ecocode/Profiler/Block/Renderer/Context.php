<?php

/**
 * Class Ecocode_Profiler_Block_Renderer_Context
 *
 * @method getBag
 */
class Ecocode_Profiler_Block_Renderer_Context
    extends Ecocode_Profiler_Block_Renderer_AbstractRenderer
{
    public function _construct()
    {
        $this->setTemplate('ecocode_profiler/renderer/context.phtml');
        parent::_construct();
    }
}
