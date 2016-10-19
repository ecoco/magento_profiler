<?php

/**
 * Class Ecocode_Profiler_Helper_Renderer
 */
class Ecocode_Profiler_Helper_Renderer
    extends Mage_Core_Helper_Abstract
{
    protected $renderer = [];

    public function renderBag($bag, array $data = [])
    {
        $data['bag'] = $bag;

        return $this->getInstance('bag')
            ->render($data);
    }

    public function renderTable($data, $labels = null)
    {
        return $this->getInstance('table')
            ->render(['items' => $data, 'labels' => $labels]);
    }

    public function renderCallStack($id, $stack, $wrap = true)
    {
        return $this->getInstance('callStack')
            ->render(['id' => $id, 'stack' => $stack, 'wrap' => $wrap]);
    }

    /**
     * @param $name
     * @return Ecocode_Profiler_Block_Renderer_RendererInterface
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
