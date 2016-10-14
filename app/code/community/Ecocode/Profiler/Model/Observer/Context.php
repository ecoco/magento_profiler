<?php

/**
 * Class Ecocode_Profiler_Model_Observer_Context
 */
class Ecocode_Profiler_Model_Observer_Context
{
    protected $helper;


    public function openBlockContext(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $block = $event->getData('block');

        if (!$this->canOpenBlockContext($block)) {
            //do not open block context as it would not get closed
            return;
        }

        $data = [
            'class'  => get_class($block),
            'module' => $block->getModuleName()
        ];

        $context = new Ecocode_Profiler_Model_Context('block::' . $block->getNameInLayout(), $data);
        $block->setData('__context', $context);

        $this->getHelper()
            ->open($context);
    }

    public function closeBlockContext(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $block = $event->getData('block');

        /** @var Ecocode_Profiler_Model_Context $context */
        if ($context = $block->getData('__context')) {
            if ($block instanceof Mage_Core_Block_Template) {
                $context->addData('template', $block->getTemplate());
            }

            $this->getHelper()
                ->close($context);
        }
    }

    /**
     * @codeCoverageIgnore
     * @return Ecocode_Profiler_Helper_Context
     */
    public function getHelper()
    {
        if ($this->helper === null) {
            $this->helper = Mage::helper('ecocode_profiler/context');
        }

        return $this->helper;
    }

    /**
     * @codeCoverageIgnore
     * @param Mage_Core_Block_Abstract $block
     * @return bool
     */
    public function canOpenBlockContext(Mage_Core_Block_Abstract $block)
    {
        if (Mage::getStoreConfig('advanced/modules_disable_output/' . $block->getModuleName())) {
            return false;
        }

        return true;
    }
}
