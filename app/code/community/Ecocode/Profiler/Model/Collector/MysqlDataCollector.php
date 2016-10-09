<?php

/**
 * Class Ecocode_Profiler_Model_Collector_MysqlDataCollector
 */
class Ecocode_Profiler_Model_Collector_MysqlDataCollector
    extends Ecocode_Profiler_Model_Collector_AbstractDataCollector
{
    const BACKTRACE_LIMIT = 10;

    protected $ignoredFunctionCalls = [
        'Mage_Core_Model_Resource_Db_Abstract::load',
        'Mage_Eav_Model_Entity_Abstract::load',
        'Mage_Catalog_Model_Resource_Abstract::load',
        'Mage_Core_Model_Abstract::load',
        'Varien_Data_Collection_Db::_fetchAll'
    ];

    protected $ignoreInstanceOf = [
        'Ecocode_Profiler_Model_Collector_MysqlDataCollector',
        'Zend_Db_Statement',
        'Zend_Db_Adapter_Abstract',
        'Varien_Db_Statement_Pdo_Mysql'
    ];

    protected $rawQueries     = [];
    protected $totalQueryTime = 0;

    public function logQuery(Ecocode_Profiler_Db_Statement_Pdo_Mysql $statement)
    {
        $connectionName   = 'unknown';
        $connectionConfig = $statement->getAdapter()->getConfig();
        if (isset($connectionConfig['connection_name'])) {
            $connectionName = $connectionConfig['connection_name'];
        }

        $this->totalQueryTime += $statement->getElapsedTime();
        $this->rawQueries[] = [
            'sql'        => $statement->getQueryString(),
            'connection' => $connectionName,
            'statement'  => $statement,
            'time'       => $statement->getElapsedTime(),
            'params'     => $statement->getParams(),
            'result'     => $statement->getResult(),
            'context'    => $this->getContextId(),
            'trace'      => $this->getTrace()
        ];
    }


    /**
     * @param Mage_Core_Controller_Request_Http  $request
     * @param Mage_Core_Controller_Response_Http $response
     * @param Exception|null                     $exception
     *
     * @return void
     */
    public function collect(Mage_Core_Controller_Request_Http $request, Mage_Core_Controller_Response_Http $response, \Exception $exception = null)
    {
        $this->data['nb_queries'] = count($this->rawQueries);
        $this->data['total_time'] = $this->totalQueryTime;

        $queries     = [];
        $connections = [];

        foreach ($this->rawQueries as $query) {
            unset($query['statement']);
            $connection = $query['connection'];
            if (!isset($connections[$connection])) {
                $connections[$connection] = 0;

            }
            $connections[$connection]++;
            $queries[] = $query;
        }
        $this->data['queries']          = $queries;
        $this->data['used_connections'] = $connections;
        $this->data;
    }

    public function getQueries()
    {
        return $this->data['queries'];
    }

    public function getConnectionData()
    {
        return $this->data['used_connections'];
    }

    public function getTotalTime()
    {
        return $this->data['total_time'];
    }


    public function getQueryCount()
    {
        return $this->data['nb_queries'];
    }


    protected function getTrace()
    {
        $backtrace = $this->getBacktrace();
        if ($backtrace === false) {
            return [];
        }

        $backtrace = $this->cleanBacktrace($backtrace);

        $backtrace = array_slice($backtrace, 0, self::BACKTRACE_LIMIT);
        $backtrace = array_map(function ($item) {
            unset($item['object'], $item['args'], $item['type']);
            return $item;
        }, $backtrace);

        return $backtrace;
    }

    public function cleanBacktrace(array $backtrace)
    {
        return Mage::helper('ecocode_profiler')
            ->cleanBacktrace(
                $backtrace,
                $this->ignoredFunctionCalls,
                $this->ignoreInstanceOf

            );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'mysql';
    }
}
