<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Monolog\Logger as BaseLogger;

/**
 * Logger.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Ecocode_Profiler_Model_Logger
    extends BaseLogger
    implements Ecocode_Profiler_Model_LoggerInterface
{

    protected static $levelMap = [
        Zend_Log::DEBUG  => self::DEBUG,
        Zend_Log::INFO   => self::INFO,
        Zend_Log::NOTICE => self::NOTICE,
        Zend_Log::WARN   => self::WARNING,
        Zend_Log::ERR    => self::ERROR,
        Zend_Log::CRIT   => self::CRITICAL,
        Zend_Log::ALERT  => self::ALERT,
        Zend_Log::EMERG  => self::EMERGENCY
    ];


    public function mageLog($level, $message, array $context = [])
    {
        $level = self::$levelMap[$level];
        return $this->log($level, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function getLogs()
    {
        if ($logger = $this->getDebugLogger()) {
            return $logger->getLogs();
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function countErrors()
    {
        if ($logger = $this->getDebugLogger()) {
            return $logger->countErrors();
        }

        return 0;
    }

    /**
     * Returns a Ecocode_Profiler_Model_Logger_DebugHandlerInterface instance if one is registered with this logger.
     *
     * @return Ecocode_Profiler_Model_Logger_DebugHandlerInterface|null A DebugLoggerInterface instance or null if none is registered
     */
    private function getDebugLogger()
    {
        foreach ($this->handlers as $handler) {
            if ($handler instanceof Ecocode_Profiler_Model_Logger_DebugHandlerInterface) {
                return $handler;
            }
        }
    }
}
