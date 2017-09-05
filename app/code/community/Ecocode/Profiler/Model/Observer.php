<?php

/**
 * Class Ecocode_Profiler_Model_Observer
 */
class Ecocode_Profiler_Model_Observer
{
    /** @var  Ecocode_Profiler_Model_Profiler */
    protected $profiler;

    protected $profiles;

    public function __construct()
    {
        $this->profiles = new \SplObjectStorage();
    }

    public function controllerFrontSendResponseBefore(Varien_Event_Observer $observer)
    {
        if (!$this->getProfiler()->isEnabled()) {
            return;
        }

        $event = $observer->getEvent();
        /** @var Mage_Core_Controller_Varien_Front $front */
        $front    = $event->getData('front');
        $request  = $front->getRequest();
        $response = $front->getResponse();
        $profile  = $this->getProfiler()->collect($front->getRequest(), $response);
        if ($profile) {
            $this->profiles[$request] = $profile;
        }

        $token = null;
        foreach ($response->getHeaders() as $header) {
            if ($header['name'] === 'X-Debug-Token') {
                $token = $header['value'];
                break;
            }
        }

        if ($token) {
            $url = Mage::helper('ecocode_profiler')->getUrl($token);
            $response->setHeader('X-Debug-Token-Link', $url);
        }


        $this->injectToolbar($response, $request, $token);
    }

    public function onTerminate()
    {
        foreach ($this->profiles as $request) {
            $this->getProfiler()->saveProfile($this->profiles[$request]);
        }
    }

    /**
     * Injects the web debug toolbar into the given Response.
     *
     * @param Mage_Core_Controller_Response_Http $response A Response instance
     * @param Mage_Core_Controller_Request_Http  $request
     * @param null                               $token
     */
    protected function injectToolbar(
        Mage_Core_Controller_Response_Http $response,
        Mage_Core_Controller_Request_Http $request,
        $token = null
    )
    {
        $content = $response->getBody();
        $pos     = strripos($content, '</body>');

        if (false !== $pos) {
            $layout = $this->getLayout();
            /** @var Ecocode_Profiler_Block_Toolbar $toolbarBlock */
            $toolbarBlock = $layout
                ->createBlock('ecocode_profiler/toolbar', 'profiler_toolbar')
                ->setData([
                    'token'   => $token,
                    'request' => $request,
                ]);

            $baseJsBlock = $layout->createBlock('core/template', 'profiler_base_js')
                ->setTemplate('ecocode_profiler/profiler/base.js.phtml');

            $toolbarBlock->setChild('base_js', $baseJsBlock);

            $toolbar = "\n" . str_replace("\n", '', $toolbarBlock->toHtml()) . "\n";
            $content = substr($content, 0, $pos) . $toolbar . substr($content, $pos);
            $response->setBody($content);
        }
    }

    /**
     * @codeCoverageIgnore
     * @return Ecocode_Profiler_Model_Profiler
     */
    protected function getProfiler()
    {
        if (!$this->profiler) {
            $this->profiler = Mage::getSingleton('ecocode_profiler/profiler');
        }

        return $this->profiler;
    }

    public function checkEvents()
    {
        if (!$this->getProfiler()->isEnabled()) {
            return;
        }

        /** @var Ecocode_Profiler_Model_AppDev $app */
        $app    = Mage::app();
        $events = $app->getEvents();

        foreach (array_keys($events) as $area) {
            $this->verifyAreaEvents($area);
        }
    }

    protected function verifyAreaEvents($area)
    {
        $config = Mage::app()->getConfig();
        $events = $config->getNode($area)->events;

        if (!$events) {
            return;
        }

        $cache    = Mage::app()->getCache();
        $cacheKey = 'profiler-events-valid-' . $area;
        $hash     = md5(json_encode($events));

        if ($cache->load($cacheKey) === $hash) {
            return;
        }

        $valid = true;

        foreach ($events->asArray() as $event) {
            foreach ($event['observers'] as $observer) {
                $modelClassName = $config->getModelClassName($observer['class']);
                if (!class_exists($modelClassName)) {
                    $valid = false;
                    Mage::log(sprintf('observer class "%s" does not exist', $modelClassName), Zend_Log::CRIT);
                }

                if (!method_exists($modelClassName, $observer['method'])) {
                    $valid = false;
                    Mage::log(
                        sprintf('observer class method "%s::%s" does not exist', $modelClassName, $observer['method']),
                        Zend_Log::WARN
                    );
                }
            }
        }

        if ($valid) {
            $cache->save($hash, $cacheKey);
        }

        return;
    }


    /**
     * @codeCoverageIgnore
     */
    public function checkRedirect()
    {
        if (Mage::getSingleton('ecocode_profiler/session')->getData('eco_redirect')) {
            Mage::app()->getRequest()->setParam('_redirected', true);
        }
    }

    public function captureFlashMessages()
    {
        $collector = $this->getProfiler()->getDataCollector('request');
        /** @var Ecocode_Profiler_Model_Collector_RequestDataCollector $collector */
        if ($collector) {
            $collector->captureFlashMessages();
        }
    }


    /**
     * @codeCoverageIgnore
     * @return Mage_Core_Model_Layout
     */
    protected function getLayout()
    {
        return Mage::app()->getLayout();
    }
}
