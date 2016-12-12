<?php

/**
 * Class Ecocode_Profiler_Model_Collector_EventDataCollector
 */
class Ecocode_Profiler_Model_Collector_EventDataCollector
    extends Ecocode_Profiler_Model_Collector_AbstractDataCollector
    implements Ecocode_Profiler_Model_Collector_LateDataCollectorInterface
{
    /**
     * {@inheritdoc}
     */
    public function collect(Mage_Core_Controller_Request_Http $request, Mage_Core_Controller_Response_Http $response, \Exception $exception = null)
    {
        $this->data = [
            'execution_time_total' => 0,
            'called_listeners'     => [],
            'fired_events'         => []
        ];
    }

    public function lateCollect()
    {
        $app = $this->getApp();

        $this->data = [];
        $this->collectedCalledListeners($app);
        $this->collectEvents($app);
    }

    protected function collectEvents(Ecocode_Profiler_Model_AppDev $app)
    {
        $eventList = $app->getFiredEvents();
        $events    = [];

        $observerList = $app->getEvents();

        foreach ($eventList as $eventName => $count) {
            $observers     = [];
            $observerCount = 0;

            foreach ($observerList as $area => $list) {
                if (!isset($list[$eventName]) || $list[$eventName] === false) {
                    continue;
                }

                $observers[$area] = [];
                foreach ($list[$eventName] as $areaObservers) {
                    $observers[$area] += $areaObservers;
                    $observerCount += count($areaObservers);
                }
            }

            $events[$eventName] = [
                'name'                 => $eventName,
                'count'                => $count,
                'observer'             => $observers,
                'observer_count'       => $observerCount,
                'execution_time_total' => 0,

            ];
        }

        $executionTimeTotal = 0;
        foreach ($app->getCalledListeners() as $listener) {
            $eventName = $listener['event_name'];
            if (!isset($events[$eventName])) {
                continue;
            }
            $executionTimeTotal += $listener['execution_time'];
            $events[$eventName]['execution_time_total'] += $listener['execution_time'];
        }

        $this->data['total_events']         = 0;
        $this->data['execution_time_total'] = $executionTimeTotal;

        $this->data['fired_events'] = $events;

        return $this;
    }

    protected function collectedCalledListeners(Ecocode_Profiler_Model_AppDev $app)
    {
        $this->data['called_listeners'] = $app->getCalledListeners();

        return $this;
    }

    public function getTotalTime()
    {
        return $this->getData('execution_time_total', 0);
    }

    public function getCalledListeners()
    {
        return $this->getData('called_listeners', []);
    }

    public function getFiredEvents()
    {
        return $this->getData('fired_events', []);
    }

    /**
     * @codeCoverageIgnore
     * @return Mage_Core_Model_App
     */
    public function getApp()
    {
        return Mage::app();
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    public function getName()
    {
        return 'event';
    }
}
