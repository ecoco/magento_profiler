<?php

interface Ecocode_Profiler_Model_LoggerInterface
 extends Ecocode_Profiler_Model_Logger_DebugHandlerInterface
{
    public function mageLog($level, $message, array $context = []);
}
