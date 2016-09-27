<?php

/**
 * Class Ecocode_Profiler_Block_Renderer_AbstractRenderer
 *
 * @method getBag
 */
class Ecocode_Profiler_Block_Renderer_AbstractRenderer
    extends Mage_Core_Block_Template
{
    protected $_templateFileCache = [];

    public function getTemplateFile()
    {
        $template = $this->getTemplate();
        $key      = $this->getArea() . '_' . $template;

        if (!isset($this->_templateFileCache[$key])) {
            $this->_templateFileCache[$key] = parent::getTemplateFile();
        }
        
        return $this->_templateFileCache[$key];
    }
}
