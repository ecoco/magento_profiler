<?php

/**
 * Class Ecocode_Profiler_Model_Collector_TimeDataCollector
 */
class Ecocode_Profiler_Model_Collector_TimeDataCollector
    extends Ecocode_Profiler_Model_Collector_AbstractDataCollector
    implements Ecocode_Profiler_Model_Collector_LateDataCollectorInterface
{
    public function isVarienProfiler()
    {
        return defined('Varien_Profiler::CATEGORY_SECTION') ? false : true;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Mage_Core_Controller_Request_Http $request, Mage_Core_Controller_Response_Http $response, \Exception $exception = null)
    {
        $this->data = [
            'start_time' => Mage::app()->getStartTime() * 1000,
            'events'     => []
        ];
    }


    public function lateCollect()
    {
        if (!$this->isVarienProfiler()) {
            $this->setEvents($this->getEventsFromProfiler());
        }

        if (!$this->getDuration()) {
            $this->data['duration'] = (microtime(true) * 1000) - $this->getData('start_time', 0);
        }

        return $this;
    }

    /**
     * Sets the request events.
     *
     * @param array $events The request events
     */
    public function setEvents(array $events)
    {
        foreach ($events as $event) {
            $event->ensureStopped();
        }

        $this->data['events'] = $events;
    }

    /**
     * Gets the request elapsed time.
     *
     * @return float The elapsed time
     */
    public function getDuration()
    {
        if (!isset($this->data['events']['__section__'])) {
            return $this->getData('duration', 0);
        }

        /** @var \Symfony\Component\Stopwatch\StopwatchEvent $event */
        $event = $this->data['events']['__section__'];

        return $event->getDuration();
    }

    /**
     * Gets the request time.
     *
     * @return int The time
     */
    public function getStartTime()
    {
        return $this->getData('start_time', 0);
    }

    public function getEvents()
    {
        return $this->getData('events', []);
    }


    /**
     * @codeCoverageIgnore
     * @return string
     */
    public function getName()
    {
        return 'time';
    }

    /**
     * @codeCoverageIgnore
     * @return []
     */
    protected function getEventsFromProfiler()
    {
        if (@class_exists('Symfony\Component\Stopwatch\Stopwatch')) {
            return Varien_Profiler::getTimers();
        }

        return [];
    }
}
