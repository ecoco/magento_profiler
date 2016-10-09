<?php

class TestHelper extends PHPUnit_Framework_TestCase
{
    static $mageAppReflection;

    protected $reInetMage = false;

    /**
     * @beforeClass
     */
    protected function setUp()
    {
        $this->initAppOnMageClass('Mage', $this->reInetMage);

    }

    public function initAppOnMageClass($class, $force = false)
    {
        $mageReflectionClass = new \ReflectionClass($class);
        $properties          = $mageReflectionClass->getStaticProperties();

        if ($force) {
            $reflectedProperty = $mageReflectionClass->getProperty('_app');
            $reflectedProperty->setAccessible(true);
            $reflectedProperty->setValue(null);
        }

        if (!isset($properties['_app']) || $force) {
            $options = [
                'cache'        => ['id_prefix' => 'test'],
                'config_model' => 'Ecocode_Profiler_Model_Core_Config'
            ];
            $class::app('', 'store', $options);
        }
    }

    /**
     * @param $data
     * @return Varien_Event_Observer
     */
    protected function getObserver($data)
    {
        $observer = new Varien_Event_Observer();
        $event    = new Varien_Event($data);
        $observer->setEvent($event);

        return $observer;
    }
}
