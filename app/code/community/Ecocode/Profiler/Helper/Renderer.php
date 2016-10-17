<?php

/**
 * Class Ecocode_Profiler_Helper_Renderer
 */
class Ecocode_Profiler_Helper_Renderer
    extends Mage_Core_Helper_Abstract
{
    protected $renderer = [];

    public function renderCallStack($id, $trace, $wrap = true)
    {
        return $this->getInstance('callStack')
            ->setData(['id' => $id, 'trace' => $trace, 'wrap' => $wrap])
            ->toHtml();
    }

    /**
     * @param $name
     * @return Ecocode_Profiler_Block_Renderer_CallStack
     */
    public function getInstance($name)
    {
        if (strpos($name, '/') === false) {
            $name = 'ecocode_profiler/renderer_' . $name;
        }

        if (!isset($this->renderer[$name])) {
            $this->renderer[$name] = Mage::app()->getLayout()->createBlock($name);
        }

        return $this->renderer[$name];
    }
}
