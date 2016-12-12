<?php

class Ecocode_Profiler_SettingsController extends Ecocode_Profiler_Controller_AbstractController
{
    /**
     * @return Ecocode_Profiler_Model_Config
     */
    protected function getConfig()
    {
        return Mage::getSingleton('ecocode_profiler/config');
    }

    /**
     * @return Ecocode_Profiler_Model_Profiler
     */
    protected function getProfiler()
    {
        return Mage::getSingleton('ecocode_profiler/profiler');
    }

    public function indexAction()
    {
        Mage::register('current_panel', 'settings');

        $request = $this->getRequest();
        $token   = $request->getParam(Ecocode_Profiler_Model_Profiler::URL_TOKEN_PARAMETER);
        if ($token && $profile = $this->getProfiler()->loadProfile($token)) {
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
            /** @var Ecocode_Profiler_Model_Config $config */
            $config = $this->getConfig();
            if ($collectorName = $request->getParam('collector')) {
                $collector = $this->getProfiler()->getDataCollector($collectorName);
                $config->saveCollectorValue($collector, $key, $value);
            } else {
                $config->saveValue($key, $value);
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
            $configHelper = $this->getConfig();
            if ($collectorName = $request->getParam('collector')) {
                $collector = $this->getProfiler()->getDataCollector($collectorName);
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
