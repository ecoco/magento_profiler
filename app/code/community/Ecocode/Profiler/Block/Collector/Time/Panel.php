<?php

/**
 * Class Ecocode_Profiler_Block_Collector_Log_Panel
 * @method Ecocode_Profiler_Model_Collector_TimeDataCollector getCollector
 */
class Ecocode_Profiler_Block_Collector_Time_Panel
    extends Ecocode_Profiler_Block_Collector_Base
{
    public function getRequestJsonData()
    {
        $eventList = $this->getCollector()->getEvents();
        $events = [];
        foreach ($eventList as $name => $event) {
            /** @var \Symfony\Component\Stopwatch\StopwatchEvent $event */
            if ($name === '__section__' || $name === 'mage') {
                continue;
            }
            $periods = [];
            foreach ($event->getPeriods() as $period) {
                $periods[] = [
                    'start' => sprintf('%F', $period->getStartTime()),
                    'end'   => sprintf('%F', $period->getEndTime())
                ];
            }

            $events[] = [
                'name'      => $name,
                'category'  => $event->getCategory(),
                'origin'    => sprintf('%F', $event->getOrigin()),
                'starttime' => sprintf('%F', $event->getStartTime()),
                'endtime'   => sprintf('%F', $event->getEndTime()),
                'duration'  => sprintf('%F', $event->getDuration()),
                'memory'    => sprintf('%.1F', $event->getMemory() / 1024 / 1024),
                'periods'   => $periods
            ];
        }

        return [
            'id'     => $this->getToken(),
            'left'   => $eventList['__section__']->getStartTime(),
            'events' => $events
        ];
    }
}
