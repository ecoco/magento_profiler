<?php

/**
 * Class Ecocode_Profiler_Model_Collector_LogDataCollector
 */
class Ecocode_Profiler_Model_Collector_LogDataCollector
    extends Ecocode_Profiler_Model_Collector_AbstractDataCollector
    implements Ecocode_Profiler_Model_Collector_LateDataCollectorInterface
{
    private $errorNames = [
        E_DEPRECATED        => 'E_DEPRECATED',
        E_USER_DEPRECATED   => 'E_USER_DEPRECATED',
        E_NOTICE            => 'E_NOTICE',
        E_USER_NOTICE       => 'E_USER_NOTICE',
        E_STRICT            => 'E_STRICT',
        E_WARNING           => 'E_WARNING',
        E_USER_WARNING      => 'E_USER_WARNING',
        E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
        E_CORE_WARNING      => 'E_CORE_WARNING',
        E_USER_ERROR        => 'E_USER_ERROR',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
        E_PARSE             => 'E_PARSE',
        E_ERROR             => 'E_ERROR',
        E_CORE_ERROR        => 'E_CORE_ERROR',
    ];

    protected $logger;

    /**
     * Ecocode_Profiler_Model_Collector_LogDataCollector constructor.
     */
    public function __construct()
    {
        $this->logger = Mage::getLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Mage_Core_Controller_Request_Http $request, Mage_Core_Controller_Response_Http $response, \Exception $exception = null)
    {
        $this->data = [
            'logs'        => [],
            'total_count' => 0
        ];
    }


    public function lateCollect()
    {
        $this->data = [];
        if ($this->logger) {
            $this->data         = $this->computeErrorsCount();
            $this->data['logs'] = $this->sanitizeLogs($this->getLogger()->getLogs());
        }
    }

    public function countErrors()
    {
        return isset($this->data['error_count']) ? $this->data['error_count'] : 0;
    }

    /**
     * Gets the logs.
     *
     * @return array An array of logs
     */
    public function getLogs()
    {
        return isset($this->data['logs']) ? $this->data['logs'] : [];
    }

    public function getPriorities()
    {
        return isset($this->data['priorities']) ? $this->data['priorities'] : [];
    }

    public function countDeprecations()
    {
        return isset($this->data['deprecation_count']) ? $this->data['deprecation_count'] : 0;
    }

    public function countScreams()
    {
        return isset($this->data['scream_count']) ? $this->data['scream_count'] : 0;
    }

    private function sanitizeLogs($logs)
    {
        $errorContextById = [];
        $sanitizedLogs    = [];

        foreach ($logs as $log) {
            $context = $this->sanitizeContext($log['context']);

            if (isset($context['type'], $context['file'], $context['line'], $context['level'])) {
                $errorId  = md5("{$context['type']}/{$context['line']}/{$context['file']}\x00{$log['message']}", true);
                $silenced = !($context['type'] & $context['level']);
                if (isset($this->errorNames[$context['type']])) {
                    $context = array_merge(['name' => $this->errorNames[$context['type']]], $context);
                }

                if (isset($errorContextById[$errorId])) {
                    if (isset($errorContextById[$errorId]['errorCount'])) {
                        ++$errorContextById[$errorId]['errorCount'];
                    } else {
                        $errorContextById[$errorId]['errorCount'] = 2;
                    }

                    if (!$silenced && isset($errorContextById[$errorId]['scream'])) {
                        unset($errorContextById[$errorId]['scream']);
                        $errorContextById[$errorId]['level'] = $context['level'];
                    }

                    continue;
                }

                $errorContextById[$errorId] = &$context;
                if ($silenced) {
                    $context['scream'] = true;
                }

                $log['context'] = &$context;
                unset($context);
            } else {
                $log['context'] = $context;
            }

            $sanitizedLogs[] = $log;
        }

        return $sanitizedLogs;
    }

    private function sanitizeContext($context)
    {
        if (is_array($context)) {
            foreach ($context as $key => $value) {
                $context[$key] = $this->sanitizeContext($value);
            }

            return $context;
        }

        if (is_resource($context)) {
            return sprintf('Resource(%s)', get_resource_type($context));
        }

        if (is_object($context)) {
            return sprintf('Object(%s)', get_class($context));
        }

        return $context;
    }

    private function computeErrorsCount()
    {
        $logger = $this->getLogger();
        $count  = [
            'total_log_count'   => count($logger->getLogs()),
            'error_count'       => $logger->countErrors(),
            'deprecation_count' => 0,
            'scream_count'      => 0,
            'priorities'        => [],
        ];

        foreach ($logger->getLogs() as $log) {
            if (isset($count['priorities'][$log['priority']])) {
                ++$count['priorities'][$log['priority']]['count'];
            } else {
                $count['priorities'][$log['priority']] = [
                    'count' => 1,
                    'name'  => $log['priorityName'],
                ];
            }

            if (isset($log['context']['type'], $log['context']['level'])) {
                if (E_DEPRECATED === $log['context']['type'] || E_USER_DEPRECATED === $log['context']['type']) {
                    ++$count['deprecation_count'];
                } elseif (!($log['context']['type'] & $log['context']['level'])) {
                    ++$count['scream_count'];
                }
            }
        }

        ksort($count['priorities']);

        return $count;
    }

    public function getLogCount()
    {
        return isset($this->data['total_log_count']) ? $this->data['total_log_count'] : 0;
    }


    /**
     * @codeCoverageIgnore
     * {@inheritdoc}
     */
    protected function getLogger()
    {
        return $this->logger;
    }


    /**
     * @codeCoverageIgnore
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'log';
    }

}
