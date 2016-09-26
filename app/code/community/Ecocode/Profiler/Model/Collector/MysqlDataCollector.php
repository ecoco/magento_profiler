<?php

class Ecocode_Profiler_Model_Collector_MysqlDataCollector
    extends Ecocode_Profiler_Model_Collector_AbstractDataCollector
{
    protected $blockPanelName = 'ecocode_profiler/collector_mysql_panel';

    protected $_currentBlock;

    protected $rawQueries     = [];
    protected $totalQueryTime = 0;

    public function logQuery(Ecocode_Profiler_Db_Statement_Pdo_Mysql $statement, array $params = [], $time, $result, $trace = [])
    {
        $connectionName   = 'unknown';
        $connectionConfig = $statement->getAdapter()->getConfig();
        if (isset($connectionConfig['connection_name'])) {
            $connectionName = $connectionConfig['connection_name'];
        }

        $this->totalQueryTime += $time;
        $this->rawQueries[] = [
            'sql'        => $statement->getQueryString(),
            'connection' => $connectionName,
            'statement'  => $statement,
            'time'       => $time,
            'params'     => $params,
            'result'     => $result,
            'context'    => $this->getContextId(),
            'trace'      => $trace
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function collect(Mage_Core_Controller_Request_Http $request, Mage_Core_Controller_Response_Http $response, \Exception $exception = null)
    {
        $this->data['nb_queries'] = count($this->rawQueries);
        $this->data['total_time'] = $this->totalQueryTime;

        $queries = [];
        $connections = [];

        foreach ($this->rawQueries as $query) {
            //@TODO add detection of type insert, delete etc
            unset($query['statement']);
            $connection = $query['connection'];
            if (!isset($connections[$connection])) {
                $connections[$connection] = 0;

            }
            $connections[$connection]++;
            $queries[] = $query;
        }
        $this->data['queries'] = $queries;
        $this->data['used_connections'] = $connections;
        $this->data;
    }

    public function getQueries()
    {
        return $this->data['queries'];
    }

    public function getConnectionData() {
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

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'mysql';
    }
}
