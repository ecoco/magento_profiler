<?php

/**
 * Class Ecocode_Profiler_Block_Collector_Log_Panel
 */
class Ecocode_Profiler_Block_Collector_Log_Panel
    extends Ecocode_Profiler_Block_Collector_Base
{
    protected $logTableRenderer;
    protected $logGroups;

    protected $priorityNames = [
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
            $logGroups = [
                'deprecation'    => [],
                'debug'          => [],
                'info_and_error' => [],
                'silenced'       => []
            ];

            /** @var Ecocode_Profiler_Model_Collector_LogDataCollector $collector */
            $collector = $this->getCollector();
            foreach ($collector->getLogs() as $log) {
                if (isset($log['context']['level'], $log['context']['type']) && in_array($log['context']['type'], [E_DEPRECATED, E_USER_DEPRECATED])) {
                    $logGroups['deprecation'][] = $log;
                } elseif (isset($log['context']['scream']) && $log['context']['scream'] === true) {
                    $logGroups['silenced'][] = $log;
                } elseif ($log['priorityName'] === 'DEBUG') {
                    $logGroups['debug'][] = $log;
                } else {
                    $logGroups['info_and_error'][] = $log;
                }
            }
            $this->logGroups = $logGroups;
        }

        return $this->logGroups;
    }

    public function getPriorityName($level)
    {
        $names = $this->priorityNames;
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


    public function renderLogTable($logs, $category = '', $showLevel = false, $isDeprecation = false)
    {
        $block = $this->getLogTableRenderer();
        $block->setData([
            'logs'           => $logs,
            'category'       => $category,
            'show_level'     => $showLevel,
            'is_deprecation' => $isDeprecation
        ]);
        return $block->toHtml();
    }

    /**
     * @return $this
     */
    public function getLogTableRenderer()
    {
        if ($this->logTableRenderer === null) {
            $this->logTableRenderer = Mage::app()->getLayout()->createBlock('ecocode_profiler/renderer_log_logTable');
        }
        return $this->logTableRenderer;
    }
}
