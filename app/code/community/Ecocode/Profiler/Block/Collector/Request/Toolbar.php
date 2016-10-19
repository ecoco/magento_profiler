<?php

/**
 * Class Ecocode_Profiler_Block_Collector_Request_Toolbar
 */
class Ecocode_Profiler_Block_Collector_Request_Toolbar
    extends Ecocode_Profiler_Block_Collector_Base
{
    public function renderHandler($controller, $route = false, $method = false)
    {
        $renderer = $this->getRendererHelper()->getInstance('request_toolbarHandler');

        return $renderer->render(
            ['controller' => $controller, 'route' => $route, 'method' => $method]
        );
    }

}
