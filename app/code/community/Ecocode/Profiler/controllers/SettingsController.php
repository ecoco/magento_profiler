<?php

class Ecocode_Profiler_SettingsController extends Ecocode_Profiler_Controller_AbstractController
{
    public function indexAction()
    {
        Mage::register('current_panel', 'settings');

        $request = $this->getRequest();
        $token   = $request->getParam(Ecocode_Profiler_Model_Profiler::URL_TOKEN_PARAMETER);
        if ($token && $profile = Mage::getSingleton('ecocode_profiler/profiler')->loadProfile($token)) {
            //make the menu available if we have a token
            Mage::register('current_profile', $profile);
        }

        $this->loadLayout('profiler_default');
        $this->renderLayout();
    }

    public function saveAction()
    {
        $request = $this->getRequest();
        $key     = $request->getParam('key');
        $value   = $request->getParam('value');

        if (!$key || !$value) {
            throw new \Exception('missing "key" or "value"');
        } else {
            /** @var Ecocode_Profiler_Model_Config $configHelper */
            $configHelper = Mage::getSingleton('ecocode_profiler/config');
            if ($collectorName = $request->getParam('collector')) {
                $collector = Mage::getSingleton('ecocode_profiler/profiler')->getDataCollector($collectorName);
                $configHelper->saveCollectorValue($collector, $key, $value);
            } else {
                $configHelper->saveValue($key, $value);
            }
        }
    }

    public function resetAction()
    {
        $request = $this->getRequest();
        $key     = $request->getParam('key');

        if (!$key) {
            throw new \Exception('missing "key"');
        } else {
            /** @var Ecocode_Profiler_Model_Config $configHelper */
            $configHelper = Mage::getSingleton('ecocode_profiler/config');
            if ($collectorName = $request->getParam('collector')) {
                $collector = Mage::getSingleton('ecocode_profiler/profiler')->getDataCollector($collectorName);
                $configHelper->deleteCollectorValue($collector, $key);
                $value = $configHelper->getValue($key);
            } else {
                $configHelper->deleteValue($key);
                $value = $configHelper->getValue($key);
            }
        }

        $this->getResponse()->setHeader('Content-Type', 'application/json');
        $this->getResponse()->setBody(json_encode(['value' => $value]));
    }
}
