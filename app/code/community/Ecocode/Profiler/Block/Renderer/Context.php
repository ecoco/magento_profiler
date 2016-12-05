<?php

/**
 * Class Ecocode_Profiler_Block_Renderer_Context
 *
 * @method getBag
 */
class Ecocode_Profiler_Block_Renderer_Context
    extends Ecocode_Profiler_Block_Renderer_AbstractRenderer
{
    /** @var Ecocode_Profiler_Helper_Code */
    protected $codeHelper;

    public function _construct()
    {
        $this->setTemplate('ecocode_profiler/renderer/context.phtml');
        parent::_construct();
    }

    public function getContextData()
    {
        /** @var Ecocode_Profiler_Model_Context $context */
        $context = $this->getData('context');

        $data = $context->getData();
        if (isset($data['class'])) {
            $reflector     = new ReflectionClass($data['class']);
            $data['class'] = $this->getCodeHelper()
                ->formatFile($reflector->getFileName(), $reflector->getStartLine(), $data['class']);
        }

        if (isset($data['template'])) {
            $template         = $this->getDesignBaseDir() . $data['template'];
            $data['template'] = $this->getCodeHelper()
                ->formatFile($template, null, $data['template']);
        }

        return $data;
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    protected function getDesignBaseDir()
    {
        return Mage::getBaseDir('design') . DS;
    }

    /**
     * @codeCoverageIgnore
     * @return Ecocode_Profiler_Helper_Code
     */
    protected function getCodeHelper()
    {
        if ($this->codeHelper === null) {
            /** @var Ecocode_Profiler_Helper_Code $codeHelper */
            $this->codeHelper = Mage::helper('ecocode_profiler/code');
        }

        return $this->codeHelper;
    }
}
