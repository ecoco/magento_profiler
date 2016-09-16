<?php

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
            'fired_events'      => [],
            'fired_event_count' => 0
        ];
    }

    public function lateCollect()
    {
        $app = Mage::app();
        if (!$app instanceof Ecocode_Profiler_Model_AppDev) {
            return;
        }

        $this->data = [
            'called_listeners'  => $this->collectedCalledListeners($app),
            'fired_events'      => $this->collectEvents($app),
            'fired_event_count' => $app->getFiredEventCount()
        ];
    }

    protected function collectEvents(Ecocode_Profiler_Model_AppDev $app)
    {
        $eventList = $app->getFiredEvents();
        $events    = [];

        foreach ($eventList as $event) {
            $name = $event['name'];
            if (!isset($events[$name])) {
                $events[$name] = [
                    'name'       => $name,
                    'count'      => 1,
                    'parameters' => []
                ];
            } else {
                $events[$name]['count']++;
            }
        }

        return $events;
    }

    protected function collectedCalledListeners(Ecocode_Profiler_Model_AppDev $app)
    {
        return $app->getCalledListeners();
    }

    public function getCalledListeners()
    {
        return $this->data['called_listeners'];
    }

    public function getFiredEvents()
    {
        return $this->data['fired_events'];
    }

    public function getName()
    {
        return 'event';
    }
}