<?php


/**
 * Ecocode_Profiler_Model_Logger_DebugHandlerInterface.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface Ecocode_Profiler_Model_Logger_DebugHandlerInterface
{
    /**
     * Returns an array of logs.
     *
     * A log is an array with the following mandatory keys:
     * timestamp, message, priority, and priorityName.
     * It can also have an optional context key containing an array.
     *
     * @return array An array of logs
     */
    public function getLogs();

    /**
     * Returns the number of errors.
     *
     * @return int The number of errors
     */
    public function countErrors();
}
