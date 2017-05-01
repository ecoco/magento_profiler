<?php

class Ecocode_Profiler_Tests_Dev_Model_Overwrite_MageCoreModelTranslateTest
    extends TestHelper
{
    protected $translationReflection = [];

    /**
     * assume that our overwrite still works like the original
     * @return Mage_Core_Model_Translate
     */
    public function testProfilerTranslatedString()
    {
        $translate = new Mage_Core_Model_Translate();
        $this->runTestTranslatedString($translate);

        return $translate;
    }

    public function testOriginalTranslatedString()
    {
        $translate = new Original_Mage_Core_Model_Translate();
        $this->runTestTranslatedString($translate);
    }

    public function runTestTranslatedString(Original_Mage_Core_Model_Translate $translate)
    {
        $this->_loadModuleTranslation($translate, [
            'test' => 'test-core'
        ], 'Mage_Core');

        $this->assertTranslation($translate, 'test-core', 'test', null);

        $this->_loadModuleTranslation($translate, [
            'test'   => 'test-page',
            'test %' => 'test-page %s'
        ], 'Mage_Page');


        $this->assertTranslation($translate, 'test-core', 'test', null);

        $this->_loadThemeTranslation($translate, ['test' => 'test-overwrite-theme']);
        $this->assertTranslation($translate, 'test-overwrite-theme', 'test');

        $this->_loadThemeTranslation($translate, ['test' => 'test-overwrite-db']);
        $this->assertTranslation($translate, 'test-overwrite-db', 'test');

        $this->assertTranslation($translate, 'test-core', 'test', 'Mage_Core');
        $this->assertTranslation($translate, 'test-page', 'test', 'Mage_Page');
        $this->assertTranslation($translate, 'test-untranslated', 'test-untranslated', 'Mage_Page');
        $this->assertTranslation($translate, 'test %s', 'test %s', 'Mage_Page');
        $this->assertTranslation($translate, 'test-page asd', 'test %', 'Mage_Page', ['asd']);


        return $translate;
    }

    /**
     * @param Mage_Core_Model_Translate $translate
     *
     * @depends testProfilerTranslatedString
     */
    public function testCounts(Mage_Core_Model_Translate $translate)
    {
        $stats = [];
        foreach ($translate->getMessages() as $message) {
            if (!isset($stats[$message['state']])) {
                $stats[$message['state']] = 0;
            }
            $stats[$message['state']]++;
        }

        $this->assertEquals(3, $stats[Mage_Core_Model_Translate::STATE_TRANSLATED]);
        $this->assertEquals(4, $stats[Mage_Core_Model_Translate::STATE_FALLBACK]);
        $this->assertEquals(1, $stats[Mage_Core_Model_Translate::STATE_MISSING]);
        $this->assertEquals(1, $stats[Mage_Core_Model_Translate::STATE_INVALID]);
    }

    /**
     * @return Mage_Core_Model_Translate
     */
    public function testThemeOverwrite()
    {
        $translate = new Mage_Core_Model_Translate();
        if (version_compare(PHP_VERSION, '5.4', '<')) {
            $this->markTestSkipped('php version must be grater than 5.4');
            return $translate;
        }

        $this->_loadModuleTranslation($translate, [
            'test' => 'test-module_C'
        ], 'Mage_Customer');

        $this->_loadModuleTranslation($translate, [
            'test' => 'test-module_P'
        ], 'Mage_Page');


        $this->assertTranslation($translate, 'test-module_C', 'test');
        $messages    = $translate->getMessages();
        $lastMessage = end($messages);

        $this->assertEquals('module (Mage_Customer)', $lastMessage['source']);
        $this->_loadThemeTranslation($translate, ['test' => 'test-overwrite-theme']);

        //assert theme translation still in place when accessed with scope
        $this->assertTranslation($translate, 'test-module_P', 'test', 'Mage_Page');
        $this->assertTranslation($translate, 'test-overwrite-theme', 'test');

        $messages    = $translate->getMessages();
        $lastMessage = end($messages);
        $this->assertEquals('theme', $lastMessage['source']);

        return $translate;
    }

    /**
     * @param Mage_Core_Model_Translate $translate
     *
     * @depends testThemeOverwrite
     * @return null
     */
    public function testDbOverwrite(Mage_Core_Model_Translate $translate)
    {
        if (version_compare(PHP_VERSION, '5.4', '<')) {
            $this->markTestSkipped('php version must be grater than 5.4');
            return;
        }
        $this->assertTranslation($translate, 'test-overwrite-theme', 'test');

        $this->_loadDbTranslation($translate, [
            'test' => 'test-overwrite-db',
        ]);

        $this->assertTranslation($translate, 'test-overwrite-db', 'test');

        $messages    = $translate->getMessages();
        $lastMessage = end($messages);
        $this->assertEquals('db', $lastMessage['source']);

        return;
    }

    protected function assertTranslation(Original_Mage_Core_Model_Translate $translate, $expect, $key, $module = null, array $params = [])
    {
        $this->assertEquals($expect, $this->__($translate, $key, $module, $params));
    }

    protected function __(Original_Mage_Core_Model_Translate $translate, $key, $module, array $params = [])
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

    protected function _loadModuleTranslation(Original_Mage_Core_Model_Translate $object, array $data, $module)
    {
        if (version_compare(PHP_VERSION, '5.4', '>=')) {
            $closure = $this->getAddDataReflection($object)->getClosure($object);
            $closure($data, $module);
        } else {
            $this->getAddDataReflection($object)->invoke($object, $data, $module);
        }
    }

    protected function _loadThemeTranslation(Original_Mage_Core_Model_Translate $object, array $data)
    {
        if (version_compare(PHP_VERSION, '5.4', '>=')) {
            $closure = $this->getAddDataReflection($object)->getClosure($object);
            $closure($data, null);
        } else {
            $this->getAddDataReflection($object)->invoke($object, $data, null);
        }
    }

    protected function _loadDbTranslation(Original_Mage_Core_Model_Translate $object, array $data)
    {
        if (version_compare(PHP_VERSION, '5.4', '>=')) {
            $closure = $this->getAddDataReflection($object)->getClosure($object);
            $closure($data, null);
        } else {
            $this->getAddDataReflection($object)->invoke($object, $data, null);
        }
    }

    protected function getAddDataReflection(Original_Mage_Core_Model_Translate $object)
    {
        $class = get_class($object);
        if (!isset($this->translationReflection[$class])) {
            $addDataMethod = new ReflectionMethod($class, '_addData');
            $addDataMethod->setAccessible(true);

            $this->translationReflection[$class] = $addDataMethod;
        }

        return $this->translationReflection[$class];
    }

}
