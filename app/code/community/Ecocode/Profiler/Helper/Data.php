<?php

/**
 * Class Ecocode_Profiler_Helper_Data
 *
 */
class Ecocode_Profiler_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $backTraceRenderer;

    public function getCollectorUrl($token, Ecocode_Profiler_Model_Collector_DataCollectorInterface $collector)
    {
        return $this->getUrl($token, $collector->getName());
    }

    public function getUrl($token = null, $panel = null)
    {
        $params = [];
        if ($token) {
            $params[Ecocode_Profiler_Model_Profiler::URL_TOKEN_PARAMETER] = $token;
        }
        if ($panel) {
            $params['panel'] = $panel;
        }

        return $this->_getUrl('_profiler/index/panel', $params);
    }

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
            $this->backTraceRenderer = Mage::app()->getLayout()->createBlock('ecocode_profiler/renderer_backTrace');
        }
        return $this->backTraceRenderer;
    }

}