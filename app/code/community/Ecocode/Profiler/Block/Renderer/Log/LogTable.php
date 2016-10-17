<?php

/**
 * Class Ecocode_Profiler_Block_Renderer_Log_LogTable
 */
class Ecocode_Profiler_Block_Renderer_Log_LogTable
    extends Ecocode_Profiler_Block_Renderer_AbstractRenderer
{
    public function _construct()
    {
        $this->setTemplate('ecocode_profiler/collector/log/panel/log-table.phtml');
        parent::_construct();
    }

    public function getCategory()
    {
        return isset($this->_data['category']) ? $this->_data['category'] : [];
    }

    public function getLogs()
    {
        return isset($this->_data['logs']) ? $this->_data['logs'] : [];
    }

    public function getShowLevel()
    {
        return isset($this->_data['show_level']) ? $this->_data['show_level'] : true;
    }

    public function isDeprecation()
    {
        return isset($this->_data['is_deprecation']) ? $this->_data['is_deprecation'] : false;
    }

    public function isChannelDefined()
    {
        $logs = $this->getLogs();
        $log  = reset($logs);
        
        return isset($log['channel']);
    }
}
