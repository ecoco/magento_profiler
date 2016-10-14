<?php

/**
 * Class Ecocode_Profiler_Model_AppDev
 */
class Ecocode_Profiler_Model_AppDev extends Mage_Core_Model_App
{
    protected $eventsFiredCount = 0;
    protected $eventFiredCount  = [];
    protected $calledListeners  = [];

    protected $startTime;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->startTime = microtime(true);
    }

    public function getStartTime()
    {
        return $this->startTime;
    }

    public function _initRequest()
    {
        $result = parent::_initRequest();

        $this->startProfiler();

        return $result;
    }

    public function setErrorHandler($handler)
    {
        if (!@class_exists('\Symfony\Component\Debug\Debug')) {
            //only call if symfony debug is not available
            parent::setErrorHandler($handler);
        }
    }


    /**
     * Initialize application cache instance
     *
     * @param array $cacheInitOptions
     * @return Mage_Core_Model_App
     */
    protected function _initCache(array $cacheInitOptions = array())
    {
        //its to early to make use of normal rewrites as they are not yet loaded
        //so just set it our self
        $this->_config->setNode('global/models/core/rewrite/cache', 'Ecocode_Profiler_Model_Core_Cache');
        return parent::_initCache($cacheInitOptions);
    }

    /**
     * start the profiler
     *
     * @return $this
     */
    protected function startProfiler()
    {
        //to early to detect if this is the admin store
        $path = $this->getRequest()->getPathInfo();
        if (substr($path, 0, 10) === '/_profiler') {
            return $this;
        }
        //this wont work if you use a custom url and we cant tell by now
        //which one is configured. even if we read the local.xml manually
        // it can still be set in the database, so for now 80/20 solution :)
        if (substr($path, 0, 6) === '/admin') {
            return $this;
        }

        Mage::getSingleton('ecocode_profiler/profiler')->init();

        return $this;
    }

    /**
     * @param $eventName
     * @param $args
     * @return $this
     */
    public function dispatchEvent($eventName, $args)
    {
        if (!isset($args['debug'])) {
            if (!isset($this->eventFiredCount[$eventName])) {
                $this->eventFiredCount[$eventName] = 0;
            }
            $this->eventFiredCount[$eventName]++;
        }

        return parent::dispatchEvent($eventName, $args);
    }

    /**
     * overwrite to log all listener calls
     *
     * @param object                $object
     * @param string                $method
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    protected function _callObserverMethod($object, $method, $observer)
    {
        $eventName               = $observer->getEvent()->getName();
        if (!$observer->getEvent()->getData('debug')) {
            $this->calledListeners[] = [
                'event_name' => $eventName,
                'class'      => get_class($object),
                'method'     => $method
            ];

            if (!method_exists($object, $method)) {
                Mage::log(
                    sprintf(
                        'event "%s" tried to call a non existing method "%s" in "%s"',
                        $eventName,
                        $method,
                        get_class($object)
                    )
                );
            }
        }

        return parent::_callObserverMethod($object, $method, $observer);
    }

    public function getEvents()
    {
        return $this->_events;
    }

    public function getFiredEventCount()
    {
        return $this->eventsFiredCount;
    }

    public function getFiredEvents()
    {
        return $this->eventFiredCount;
    }

    public function getCalledListeners()
    {
        return $this->calledListeners;
    }
}
