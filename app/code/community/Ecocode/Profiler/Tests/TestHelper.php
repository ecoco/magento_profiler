<?php

class TestHelper extends PHPUnit_Framework_TestCase
{
    protected static $mageAppReflection;

    protected $reInetMage = false;

    protected $mageDefaultProperties = [
        '_registry' => [],
        '_isDownloader' => false,
        '_isDeveloperMode' => false,
        'headersSentThrowsException' => true,
    ];

    /**
     * @beforeClass
     */
    protected function setUp()
    {
        $this->initAppOnMageClass($this->reInetMage);
    }

    public function resetMage()
    {
        if (!isset($this->mageDefaultProperties['_appRoot'])) {
            //save to original mage root
            $this->mageDefaultProperties['_appRoot'] = Mage::getRoot();
        }
        $mageReflectionClass = new \ReflectionClass('Mage');
        $properties          = $mageReflectionClass->getStaticProperties();

        foreach($properties as $key => $value) {
            $reflectedProperty = $mageReflectionClass->getProperty($key);
            $reflectedProperty->setAccessible(true);
            $value = null;
            if (isset($this->mageDefaultProperties[$key])) {
                $value = $this->mageDefaultProperties[$key];
            }
            $reflectedProperty->setValue($value);
        }
        $this->initApp();
    }

    public function initAppOnMageClass($force = false)
    {
        $mageReflectionClass = new \ReflectionClass('Mage');

        if ($force) {
            $reflectedProperty = $mageReflectionClass->getProperty('_app');
            $reflectedProperty->setAccessible(true);
            $reflectedProperty->setValue(null);
        }
        $this->initApp();
    }

    protected function initApp(Mage_Core_Model_App $app = null)
    {
        $options = [
            'cache'        => ['id_prefix' => 'dev-test'],
            'config_model' => 'Ecocode_Profiler_Model_Core_Config'
        ];
        if ($app) {
            $app->init('', 'store', $options);
        } else {
            Mage::app('', 'store', $options);
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

    public function getProtectedValue($object, $property)
    {
        $property = new ReflectionProperty(get_class($object), $property);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}
