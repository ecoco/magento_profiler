<?php

/**
 * Class Ecocode_Profiler_Helper_Data
 *
 */
class Ecocode_Profiler_Helper_Data extends Mage_Core_Helper_Abstract
{

    protected $backTraceRenderer;


    public function renderBackTrace($id, $trace)
    {
        return $this->getBackTraceRenderer()
            ->setData(['id' => $id, 'trace' => $trace])
            ->toHtml();
    }

    /**
     * @return Ecocode_Profiler_Block_BackTrace
     */
    public function getBackTraceRenderer()
    {
        if ($this->backTraceRenderer === null) {
            $this->backTraceRenderer = Mage::app()->getLayout()->createBlock('ecocode_profiler/backTrace');
        }
        return $this->backTraceRenderer;
    }

}