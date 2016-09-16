<?php

class Ecocode_Profiler_Model_Collector_MysqlDataCollector
    extends Ecocode_Profiler_Model_Collector_AbstractDataCollector
{
    protected $blockPanelName = 'ecocode_profiler/collector_mysql_panel';

    protected $_currentBlock;

    protected $rawQueries     = [];
    protected $totalQueryTime = 0;

    public function setCurrentBlock(Varien_Event_Observer $observer)
    {
        $event               = $observer->getEvent();
        $this->_currentBlock = $event->getData('block');
        Ecocode_Profiler_Db_Statement_Pdo_Mysql::setCurrentBlock($this->_currentBlock);
    }


    public function getConnections()
    {
        return Mage::getSingleton('core/resource')->getConnections();
    }

    public function init()
    {
        foreach ($this->getConnections() as $connection) {
            /** @var Magento_Db_Adapter_Pdo_Mysql $connection */
            $connection->setStatementClass('Ecocode_Profiler_Db_Statement_Pdo_Mysql');
        }
    }

    public function logQuery(Ecocode_Profiler_Db_Statement_Pdo_Mysql $statement, array $params = [], $time, $result, $trace = [])
    {
        $context = 'unknown';
        if ($this->_currentBlock) {
            $context = $this->_currentBlock->getNameInLayout();
            $context .= '_' . get_class($this->_currentBlock);
        }

        $this->totalQueryTime += $time;
        $this->rawQueries[] = [
            'sql'       => $statement->getQueryString(),
            'statement' => $statement,
            'time'      => $time,
            'params'    => $params,
            'result'    => $result,
            'context'   => $context,
            'trace'     => $trace
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
        foreach($this->rawQueries as $query) {
            unset($query['statement']);
            $queries[] = $query;
        }
        $this->data['queries'] = $queries;
        $this->data;
    }

    public function getQueries()
    {
        return $this->data['queries'];
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