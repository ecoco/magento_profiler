<?php

/**
 * Class Ecocode_Profiler_Db_Statement_Pdo_Mysql
 *
 */
class Ecocode_Profiler_Db_Statement_Pdo_Mysql extends Varien_Db_Statement_Pdo_Mysql
{
    protected $ignoredFunctionCalls = [
        'Mage_Core_Model_Resource_Db_Abstract::load',
        'Mage_Eav_Model_Entity_Abstract::load',
        'Mage_Catalog_Model_Resource_Abstract::load',
        'Mage_Core_Model_Abstract::load',
        'Varien_Data_Collection_Db::_fetchAll'
    ];

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
        $start  = microtime(true);
        $result = parent::_executeWithBinding($params);

        $this->log($params, microtime(true) - $start, $result);
        return $result;
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
        $start  = microtime(true);
        $result = parent::_execute($params);

        $this->log($params, microtime(true) - $start, $result);


        return $result;
    }

    protected function log(array $params = null, $time, $result)
    {
        $trace = $this->getTrace();

        Mage::getSingleton('ecocode_profiler/collector_mysqlDataCollector')
            ->logQuery($this, $params, $time, $result, $trace);
    }

    public function getQueryString()
    {
        return $this->_stmt->queryString;
    }

    protected function getTrace()
    {
        if (!function_exists('debug_backtrace')) {
            return false;
        }
        $backtrace = debug_backtrace();
        //remove log

        while ($this->_shouldRemoveTraceItem(reset($backtrace))) {
            array_shift($backtrace);
        }
        $backtrace = array_slice($backtrace, 0, 10);
        $backtrace = array_map(function ($item) {
            unset($item['object'], $item['args'], $item['type']);
            return $item;
        }, $backtrace);

        return $backtrace;
    }

    protected function _shouldRemoveTraceItem($data)
    {
        if (!isset($data['class'], $data['function'])) {
            return true;
        }

        if (!isset($data['object'])) {
            return false;
        }

        $functionIdent = $data['class'] . '::' . $data['function'];
        if (in_array($functionIdent, $this->ignoredFunctionCalls)) {
            return false;
        }

        $object = $data['object'];
        return ($object instanceof Zend_Db_Adapter_Abstract
            || $object instanceof Varien_Db_Statement_Pdo_Mysql);
    }
}
