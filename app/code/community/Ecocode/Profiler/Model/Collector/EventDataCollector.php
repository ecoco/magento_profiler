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
            'called_listeners'  => [],
            'fired_events'      => []
        ];
    }

    public function lateCollect()
    {
        $app = $this->getApp();

        $this->data = [
            'called_listeners'  => $this->collectedCalledListeners($app),
            'fired_events'      => $this->collectEvents($app)
        ];
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
                'name'           => $eventName,
                'count'          => $count,
                'observer'       => $observers,
                'observer_count' => $observerCount
            ];
        }

        return $events;
    }

    protected function collectedCalledListeners(Ecocode_Profiler_Model_AppDev $app)
    {
        return $app->getCalledListeners();
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
