<?php

class Ecocode_Profiler_Block_Collector_Mysql_Panel
    extends Ecocode_Profiler_Block_Collector_Base
{


    protected $sqlHelper;
    protected $queryTableRenderer;

    public function _construct()
    {
        $this->setTemplate('ecocode_profiler/collector/mysql/panel.phtml');
        parent::_construct();
    }

    public function getIdenticalQueries()
    {
        /** @var Ecocode_Profiler_Model_Collector_MysqlDataCollector $collector */
        $collector = $this->getCollector();
        $list      = [];
        foreach ($collector->getQueries() as $queryData) {
            $ident = md5($queryData['sql'] . implode(',', $queryData['params']));
            if (!isset($list[$ident])) {
                $list[$ident] = [
                    'ident'      => $ident,
                    'count'      => 0,
                    'total_time' => 0,
                    'query'      => $queryData,
                    'traces'     => []
                ];
            }

            $list[$ident]['count']++;
            $list[$ident]['total_time'] += $queryData['time'];
            $list[$ident]['traces'][] = $queryData['trace'];
        }

        usort($list, function ($a, $b) {
            return $b['count'] - $a['count'];
        });

        $list = array_filter($list, function ($item) {
            return $item['count'] > 1;
        });

        return array_values($list);
    }


    public function getByContext()
    {
        /** @var Ecocode_Profiler_Model_Collector_MysqlDataCollector $collector */
        $collector   = $this->getCollector();
        $contextList = [];
        foreach ($collector->getQueries() as $queryData) {
            $contextKey = $queryData['context'];
            if (!isset($contextList[$contextKey])) {
                $contextList[$contextKey] = [
                    'name'       => $contextKey,
                    'count'      => 0,
                    'total_time' => 0,
                    'queries'    => []
                ];
            }

            $contextList[$contextKey]['count']++;
            $contextList[$contextKey]['total_time'] += $queryData['time'];
            $contextList[$contextKey]['queries'][] = $queryData;
        }
        usort($contextList, function ($a, $b) {
            return $b['count'] - $a['count'];
        });
        return array_values($contextList);
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

    public function renderQueryTable($prefix, array $queries)
    {
        $prefix .= '-';
        $block = $this->getQueryTableRenderer();
        $block->setData([
            'queries' => $queries,
            'prefix'  => $prefix
        ]);
        return $block->toHtml();
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

    /**
     * @return $this
     */
    public function getQueryTableRenderer()
    {
        if ($this->queryTableRenderer === null) {
            $this->queryTableRenderer = clone $this;
            $this->queryTableRenderer->setTemplate('ecocode_profiler/collector/mysql/panel/query.phtml');
        }
        return $this->queryTableRenderer;
    }

}