<?php

/**
 * Class Ecocode_Profiler_Db_Statement_Pdo_Mysql
 *
 */
class Ecocode_Profiler_Db_Statement_Pdo_Mysql extends Varien_Db_Statement_Pdo_Mysql
{
    protected $elapsedTime;
    protected $params;
    protected $result;

    /**
     * Executes statement with binding values to it.
     * Allows transferring specific options to DB driver.
     *
     * @param array $params Array of values to bind to parameter placeholders.
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function _executeWithBinding(array $params)
    {
        $this->params = $params;
        $start        = microtime(true);

        $this->result = parent::_executeWithBinding($params);

        $this->elapsedTime = microtime(true) - $start;
        $this->log();
        return $this->result;
    }

    /**
     * Executes a prepared statement.
     *
     * @param array $params OPTIONAL Values to bind to parameter placeholders.
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function _execute(array $params = null)
    {
        $this->params = $params;
        $start        = microtime(true);

        $this->result = parent::_execute($params);

        $this->elapsedTime = microtime(true) - $start;
        $this->log();

        return $this->result;
    }

    protected function log()
    {
        $this->getCollector()->logQuery($this);
    }

    /**
     * @return Ecocode_Profiler_Model_Collector_MysqlDataCollector
     */
    public function getCollector()
    {
        return Mage::getSingleton('ecocode_profiler/collector_mysqlDataCollector');
    }

    public function getQueryString()
    {
        return $this->_stmt->queryString;
    }

    /**
     * @return mixed
     */
    public function getElapsedTime()
    {
        return $this->elapsedTime;
    }

    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }
}
