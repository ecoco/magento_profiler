<?php

/**
 * Interface Ecocode_Profiler_Model_Collector_LateDataCollectorInterface
 */
interface Ecocode_Profiler_Model_Collector_LateDataCollectorInterface
{
    /**
     * Collects data as late as possible.
     */
    public function lateCollect();
}
