<?php

/**
 * Class Ecocode_Profiler_Helper_Context
 */
class Ecocode_Profiler_Helper_Context
    extends Ecocode_Profiler_Helper_AbstractHelper
{
    protected $stack = [];

    protected $list = [];

    protected $contextRenderer;

    public function __construct()
    {
        $this->open(new Ecocode_Profiler_Model_Context('unknown'));
    }

    /**
     * open a new context
     *
     * @param Ecocode_Profiler_Model_ContextInterface $context
     */
    public function open(Ecocode_Profiler_Model_ContextInterface $context)
    {
        $id = $context->getId();
        if ($current = $this->getCurrent()) {
            $context->setParentId($current->getId());
        }

        $this->list[$id]  = $context;
        $this->stack[$id] = $context;
    }

    /**
     * close a previously opened context
     *
     * @param Ecocode_Profiler_Model_ContextInterface $context
     * @return $this
     * @throws Exception
     */
    public function close(Ecocode_Profiler_Model_ContextInterface $context)
    {
        $id = $context->getId();
        if (!isset($this->stack[$id])) {
            throw new \RuntimeException('unable to close unknown context');
        }
        unset($this->stack[$id]);

        return $this;
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
            ->render(['prefix' => $prefix, 'context' => $context]);
    }

    public function getContextById($id)
    {
        $profile = $this->getCurrentProfile();
        return $profile->getCollector('context')->getById($id);
    }


    /**
     * @return Ecocode_Profiler_Block_Renderer_Context
     * @codeCoverageIgnore
     */
    public function getContextRenderer()
    {
        if ($this->contextRenderer === null) {
            $this->contextRenderer = Mage::app()->getLayout()->createBlock('ecocode_profiler/renderer_context');
        }
        return $this->contextRenderer;
    }
}
