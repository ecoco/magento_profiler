<?php

/**
 * Class Ecocode_Profiler_Block_Bag
 *
 * @method getBag
 */
class Ecocode_Profiler_Block_Renderer_CallStack
    extends Ecocode_Profiler_Block_Renderer_AbstractRenderer
{
    public function _construct()
    {
        $this->setTemplate('ecocode_profiler/renderer/call-stack.phtml');
        parent::_construct();
    }

    public function getStackId()
    {
        $id = $this->getData('id');
        if (!$id) {
            $this->setData('id', uniqid());
        }

        return $id;
    }

    public function getStack()
    {
        $trace = $this->getData('stack');
        if (!$trace) {
            return [];
        }

        return $trace;
    }

    public function shouldWarp()
    {
        $wrap = $this->getData('wrap');

        return $wrap === null ? true : $wrap;
    }
}
