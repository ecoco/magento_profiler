<?php

class Ecocode_Profiler_Model_AppDev extends Mage_Core_Model_App
{
    protected $eventFiredCount = 0;
    protected $eventsFired     = [];
    protected $calledListeners = [];

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
        //DONT SET WE ARE USING THE SYMFONY DEBUG ONE
    }

    public function init($code, $type = null, $options = [])
    {
        $context = new Ecocode_Profiler_Model_Context('init');

        $helper = Mage::helper('ecocode_profiler/context');
        $helper->open($context);
        $result = parent::init($code, $type, $options);
        $helper->close($context);
        return $result;
    }

    protected function _initModules()
    {
        if (!$this->_config->loadModulesCache()) {
            $this->_config->loadModules();
            if ($this->_config->isLocalConfigLoaded() && !$this->_shouldSkipProcessModulesUpdates()) {
                Varien_Profiler::start('mage::app::init::apply_db_schema_updates');
                Mage_Core_Model_Resource_Setup::applyAllUpdates();
                Varien_Profiler::stop('mage::app::init::apply_db_schema_updates');
            }
            /* start  */
            /* load development.xml for all modules if present */
            $this->_config->loadModulesConfiguration(['development.xml'], $this->_config);
            /* end */
            $this->_config->loadDb();
            $this->enableSymlinks();
            $this->_config->saveCache();
        }
        return $this;
    }

    protected function enableSymlinks()
    {
        $dir = $this->_config->getModuleDir('etc', 'Ecocode_Profiler');
        if (is_link($dir)) {
            //module is install via symlinks, so make sure magento allows it!
            $this->_config->setNode('default/dev/template/allow_symlink', 1);
        }
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
        $this->eventFiredCount++;
        $this->eventsFired[] = [
            'name' => $eventName,
            'args' => $args
        ];

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
        $this->calledListeners[] = [
            'event_name' => $eventName,
            'class'      => get_class($object),
            'method'     => $method
        ];

        return parent::_callObserverMethod($object, $method, $observer);
    }

    public function getFiredEvents()
    {
        return $this->eventsFired;
    }

    public function getFiredEventCount()
    {
        return $this->eventFiredCount;
    }

    public function getCalledListeners()
    {
        return $this->calledListeners;
    }
}