<?php

/**
 * Class Ecocode_Profiler_Block_Collector_Log_Panel
 *
 */
class Ecocode_Profiler_Block_Collector_Log_Panel
    extends Ecocode_Profiler_Block_Collector_Base
{
    protected $logGroups;

    const PRIORITY_NAMES = [
        Zend_Log::EMERG  => 'emergency',
        Zend_Log::ALERT  => 'emergency',
        Zend_Log::CRIT   => 'critical',
        Zend_Log::ERR    => 'error',
        Zend_Log::WARN   => 'warning',
        Zend_Log::NOTICE => 'notice',
        Zend_Log::INFO   => 'info',
        Zend_Log::DEBUG  => 'debug',
    ];


    public function getLogGroups()
    {
        if ($this->logGroups === null) {
            /** @var Ecocode_Profiler_Model_Collector_LogDataCollector $collector */
            $collector = $this->getCollector();
            $logGroups = [];
            foreach ($collector->getLogs() as $entry) {
                list($file, $level, $message) = $entry;
                if (!isset($logGroups[$file])) {
                    $logGroups[$file] = [
                        'name' => basename($file, '.log'),
                        'file' => $file,
                        'logs' => []
                    ];
                }

                $logGroups[$file]['logs'][] = [
                    'level'   => $level,
                    'message' => $message
                ];
            }
            $this->logGroups = $logGroups;
        }

        return $this->logGroups;
    }

    public function getPriorityName($level)
    {
        $names = $this::PRIORITY_NAMES;
        if (isset($names[$level])) {
            return $names[$level];
        }

        return 'unknown';
    }

    public function getEntryCssClass($level)
    {
        if ($level <= 3) {
            return 'status-error';
        }
        if ($level <= 5) {
            return 'status-warning';
        }

        return '';
    }


    public function renderTable(array $messages)
    {
        $tableBlock = $this->getLayout()->createBlock('core/template');
        $tableBlock->setTemplate('ecocode_profiler/collector/translation/panel/table.phtml');
        $tableBlock->setData('messages', $messages);
        return $tableBlock->toHtml();
    }
}