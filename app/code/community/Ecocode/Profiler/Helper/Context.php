<?php

class Ecocode_Profiler_Helper_Context
{
    protected $stack = [];

    protected $list = [];

    protected $contextRenderer;

    public function __construct()
    {
        $this->open(new Ecocode_Profiler_Model_Context('unknown'));
    }

    public function open(Ecocode_Profiler_Model_ContextInterface $context)
    {
        $id       = $context->getId();
        if ($current = $this->getCurrent()) {
            $context->setParentId($current->getId());
        }

        $this->list[$id]  = $context;
        $this->stack[$id] = $context;
    }

    public function close(Ecocode_Profiler_Model_ContextInterface $context)
    {
        $id = $context->getId();
        if (!isset($this->stack[$id])) {
            throw new Exception('suxxs');
        }
        unset($this->stack[$id]);
    }

    public function getList()
    {
        return $this->list;
    }

    public function getCurrent()
    {
        return end($this->stack);
    }

    public function getCurrentId()
    {
        $current = $this->getCurrent();
        if (!$current) {
            return null;
        }
        return $current->getId();
    }

    public function getStack()
    {
        return $this->stack;
    }


    public function render($prefix, $contextId)
    {
        $context = $this->getContextById($contextId);
        return $this->getContextRenderer()
            ->setData(['prefix' => $prefix, 'context' => $context])
            ->toHtml();
    }

    public function getContextById($id)
    {
        $profile = Mage::registry('current_profile');
        return $profile->getCollector('context')->getById($id);
    }



    /**
     * @return Ecocode_Profiler_Block_BackTrace
     */
    public function getContextRenderer()
    {
        if ($this->contextRenderer === null) {
            $this->contextRenderer = Mage::app()->getLayout()->createBlock('ecocode_profiler/renderer_context');
        }
        return $this->contextRenderer;
    }
}