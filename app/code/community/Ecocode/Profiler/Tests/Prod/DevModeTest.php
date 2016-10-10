<?php

class Ecocode_Profiler_Tests_DevModeTest
    extends TestHelper
{


    public function testDisabledInProduction()
    {
        $app = Mage::app();

        $this->assertNotInstanceOf(
            'Ecocode_Profiler_Model_AppDev',
            $app
        );
    }

    public function testCollectorsNotLoaded()
    {
        $app = Mage::app();

        $value = $app->getConfig()->getNode('ecocode/profiler');
        $this->assertFalse($value);
    }

    public function testModelsNotLoaded()
    {
        $app = Mage::app();

        $value = $app->getConfig()->getNode('global/models/ecocode_profiler');
        $this->assertFalse($value);
    }
}
