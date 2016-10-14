<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


use Monolog\Logger;
use Monolog\Handler\TestHandler;

/**
 * DebugLogger.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class Ecocode_Profiler_Model_Logger_DebugHandler extends TestHandler
    implements Ecocode_Profiler_Model_Logger_DebugHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getLogs()
    {
        $records = [];
        foreach ($this->records as $record) {
            $records[] = [
                'timestamp'    => $record['datetime']->getTimestamp(),
                'message'      => $record['message'],
                'priority'     => $record['level'],
                'priorityName' => $record['level_name'],
                'context'      => $record['context'],
                'channel'      => isset($record['channel']) ? $record['channel'] : '',
            ];
        }

        return $records;
    }

    /**
     * {@inheritdoc}
     */
    public function countErrors()
    {
        $cnt    = 0;
        $levels = [Logger::ERROR, Logger::CRITICAL, Logger::ALERT, Logger::EMERGENCY];
        foreach ($levels as $level) {
            if (isset($this->recordsByLevel[$level])) {
                $cnt += count($this->recordsByLevel[$level]);
            }
        }

        return $cnt;
    }
}

