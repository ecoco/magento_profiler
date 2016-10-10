<?php

/**
 * Class Ecocode_Profiler_Block_Renderer_Context
 */
class Ecocode_Profiler_Block_Renderer_Mysql_QueryTable
    extends Ecocode_Profiler_Block_Renderer_AbstractRenderer
{
    protected $sqlHelper;

    public function _construct()
    {
        $this->setTemplate('ecocode_profiler/collector/mysql/panel/query-table.phtml');
        parent::_construct();
    }


    public function replaceQueryParameters($query, array $parameters)
    {
        return $this->getSqlHelper()->replaceQueryParameters($query, $parameters);
    }

    public function dumpParameters(array $parameters)
    {
        return $this->getSqlHelper()->dumpParameters($parameters);
    }

    public function formatQuery($sql, $highlightOnly = false)
    {
        return $this->getSqlHelper()->formatQuery($sql, $highlightOnly);
    }

    /**
     * @return Ecocode_Profiler_Helper_Sql
     */
    public function getSqlHelper()
    {
        if ($this->sqlHelper === null) {
            $this->sqlHelper = Mage::helper('ecocode_profiler/sql');
        }
        return $this->sqlHelper;
    }
}
