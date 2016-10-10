<?php

/**
 * Class Ecocode_Profiler_Helper_Renderer
 */
class Ecocode_Profiler_Helper_Renderer
    extends Mage_Core_Helper_Abstract
{
    protected $backTraceRenderer;

    public function get($name)
    {

    }


    public function renderBackTrace($id, $trace)
    {
        return $this->getBackTraceRenderer()
            ->setData(['id' => $id, 'trace' => $trace])
            ->toHtml();
    }

    /**
     * @return Ecocode_Profiler_Block_Renderer_BackTrace
     */
    public function getBackTraceRenderer()
    {
        if ($this->backTraceRenderer === null) {
            $this->backTraceRenderer = Mage::app()->getLayout()->createBlock('ecocode_profiler/renderer_backTrace');
        }
        return $this->backTraceRenderer;
    }
}
