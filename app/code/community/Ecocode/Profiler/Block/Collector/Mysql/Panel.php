<?php

/**
 * Class Ecocode_Profiler_Block_Collector_Mysql_Panel
 */
class Ecocode_Profiler_Block_Collector_Mysql_Panel
    extends Ecocode_Profiler_Block_Collector_Base
{


    protected $sqlHelper;
    protected $queryTableRenderer;

    protected $queries;
    protected $identicalQueries;
    protected $queriesByContext;
    protected $queryCountByType;

    public function _construct()
    {
        //ban cache usage as we dont need the cache and it causes some overhead
        //only ban it here as we would get some issues on the cache panel
        $this->banBlockCacheUsage();

        $this->setTemplate('ecocode_profiler/collector/mysql/panel.phtml');
        parent::_construct();
    }

    public function prepareQueryData()
    {
        $this->queries          = [];
        $this->identicalQueries = [];
        $this->queriesByContext = [];
        $this->queryCountByType = [
            'select' => 0,
            'insert' => 0,
            'update' => 0,
            'delete' => 0
        ];

        /** @var Ecocode_Profiler_Model_Collector_MysqlDataCollector $collector */
        $collector = $this->getCollector();

        foreach ($collector->getQueries() as &$queryData) {
            $this->processType($queryData);
            $this->preRenderQuery($queryData);
            $this->processIdentical($queryData);
            $this->processContext($queryData);
            $this->queries[] = $queryData;
        }

        usort($this->queriesByContext, function ($context1, $context2) {
            return $context2['count'] - $context1['count'];
        });

        usort($this->identicalQueries, function ($query1, $query2) {
            return $query2['count'] - $query1['count'];
        });

        $this->identicalQueries = array_filter($this->identicalQueries, function ($item) {
            return $item['count'] > 1;
        });


        return $this;
    }

    protected function processType(array &$queryData)
    {
        $type = $this->getQueryType($queryData['sql']);

        if (isset($this->queryCountByType[$type])) {
            $this->queryCountByType[$type]++;
        }

        $queryData['type'] = $type;
    }

    protected function getQueryType($sql)
    {
        $type = explode(' ', $sql, 2);
        $type = reset($type);
        $type = strtolower($type);

        return $type;
    }

    protected function processIdentical(array $queryData)
    {
        $queryId = $this->getQueryId($queryData);
        $time    = $queryData['time'];
        $trace   = $queryData['trace'];

        if (!isset($this->identicalQueries[$queryId])) {
            //unset query specif data
            unset($queryData['time'], $queryData['trace']);
            $this->identicalQueries[$queryId] = [
                'id'         => $queryId,
                'count'      => 0,
                'total_time' => 0,
                'query'      => $queryData,
                'traces'     => []
            ];
        }
        $this->identicalQueries[$queryId]['count']++;
        $this->identicalQueries[$queryId]['total_time'] += $time;
        $this->identicalQueries[$queryId]['traces'][] = $trace;
    }

    /**
     * @param array $queryData
     * @return string
     */
    protected function getQueryId(array $queryData)
    {
        $params = isset($queryData['params']) ? $queryData['params'] : [];

        return md5($queryData['sql'] . implode(',', $params));
    }

    /**
     * @param array $queryData
     */
    protected function processContext(array $queryData)
    {
        $contextKey = $queryData['context'];
        if (!isset($this->queriesByContext[$contextKey])) {
            $this->queriesByContext[$contextKey] = [
                'name'       => $contextKey,
                'count'      => 0,
                'total_time' => 0,
                'queries'    => []
            ];
        }

        $this->queriesByContext[$contextKey]['count']++;
        $this->queriesByContext[$contextKey]['total_time'] += $queryData['time'];
        $this->queriesByContext[$contextKey]['queries'][] = $queryData;
    }

    public function getIdenticalQueries()
    {
        if ($this->identicalQueries === null) {
            $this->prepareQueryData();
        }
        return $this->identicalQueries;
    }

    public function preRenderQuery(array &$queryData)
    {
        $queryTableRenderer = $this->getQueryTableRenderer();

        $queryData['sql_highlighted'] = $queryTableRenderer->formatQuery($queryData['sql'], true);
        $queryData['sql_formatted']   = $queryTableRenderer->formatQuery($queryData['sql']);
        $queryData['sql_runnable']    = $queryTableRenderer->formatQuery(
            $queryTableRenderer->replaceQueryParameters($queryData['sql'], $queryData['params']),
            true
        );
    }

    public function getQueries()
    {
        if ($this->queries === null) {
            $this->prepareQueryData();
        }
        return $this->queries;
    }

    public function getQueryCountByType()
    {
        if ($this->queryCountByType === null) {
            $this->prepareQueryData();
            $this->queryCountByType = array_filter($this->queryCountByType);
        }

        return $this->queryCountByType;
    }

    public function getByContext()
    {
        if ($this->queriesByContext === null) {
            $this->prepareQueryData();
        }

        return $this->queriesByContext;
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
     * @return $this
     */
    public function getQueryTableRenderer()
    {
        if ($this->queryTableRenderer === null) {
            $this->queryTableRenderer = Mage::app()->getLayout()->createBlock('ecocode_profiler/renderer_mysql_queryTable');
        }
        return $this->queryTableRenderer;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function banBlockCacheUsage()
    {
        Mage::app()->getCacheInstance()->banUse(Mage_Core_Block_Abstract::CACHE_GROUP);
    }
}
