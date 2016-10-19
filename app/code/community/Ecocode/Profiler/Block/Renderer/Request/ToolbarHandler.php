<?php

/**
 * Class Ecocode_Profiler_Block_Renderer_Request_ToolbarHandler
 *
 * @method getMethod
 * @method getRoute
 * @method getController
 */
class Ecocode_Profiler_Block_Renderer_Request_ToolbarHandler
    extends Ecocode_Profiler_Block_Renderer_AbstractRenderer
{
    public function _construct()
    {
        $this->setTemplate('ecocode_profiler/collector/request/toolbar/handler.phtml');
        parent::_construct();
    }
}

