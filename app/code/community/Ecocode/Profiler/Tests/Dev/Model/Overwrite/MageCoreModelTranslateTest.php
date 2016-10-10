<?php

class Ecocode_Profiler_Tests_Dev_Model_Overwrite_MageCoreModelTranslateTest
    extends TestHelper
{
    public function testTranslatedString()
    {
        $translate = new Mage_Core_Model_Translate();


        $this->addTranslations($translate, [
            'test' => 'test-core'
        ], 'Mage_Core');

        $this->addTranslations($translate, [
            'test'   => 'test-page',
            'test %' => 'test-page %s',
        ], 'Mage_Page');


        $this->assertTranslation($translate, 'test-core', 'test', 'none');
        $this->assertTranslation($translate, 'test-core', 'test', 'Mage_Core');
        $this->assertTranslation($translate, 'test-page', 'test', 'Mage_Page');
        $this->assertTranslation($translate, 'test-untranslated', 'test-untranslated', 'Mage_Page');
        $this->assertTranslation($translate, 'test %s', 'test %s', 'Mage_Page');
        $this->assertTranslation($translate, 'test-page asd', 'test %', 'Mage_Page', ['asd']);

        $stats = [];
        foreach ($translate->getMessages() as $message) {
            if (!isset($stats[$message['state']])) {
                $stats[$message['state']] = 0;
            }
            $stats[$message['state']]++;
        }

        $this->assertEquals(3, $stats[Mage_Core_Model_Translate::STATE_TRANSLATED]);
        $this->assertEquals(1, $stats[Mage_Core_Model_Translate::STATE_FALLBACK]);
        $this->assertEquals(1, $stats[Mage_Core_Model_Translate::STATE_MISSING]);
        $this->assertEquals(1, $stats[Mage_Core_Model_Translate::STATE_INVALID]);
    }

    protected function assertTranslation(Mage_Core_Model_Translate $translate, $expect, $key, $module, array $params = [])
    {
        $this->assertEquals($expect, $this->__($translate, $key, $module, $params));
    }

    protected function __(Mage_Core_Model_Translate $translate, $key, $module, array $params = [])
    {
        $expr = new Mage_Core_Model_Translate_Expr($key, $module);

        $args = $params;
        array_unshift($args, $expr);
        return $translate->translate($args);
    }


    protected function addTranslations($translate, $data, $scope)
    {
        $addDataMethod = new ReflectionMethod('Mage_Core_Model_Translate', '_addData');
        $addDataMethod->setAccessible(true);

        $addDataMethod->invoke($translate, $data, $scope);

        return $translate;
    }
}
