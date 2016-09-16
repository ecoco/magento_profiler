<?php

/**
 * Class Ecocode_Profiler_Model_Observer
 *
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

    /**
     * @return Ecocode_Profiler_Model_Profiler
     */
    public function getProfiler()
    {
        if (!$this->profiler) {
            $this->profiler = Mage::getSingleton('ecocode_profiler/profiler');
        }

        return $this->profiler;
    }


    public function startProfiler()
    {
        //to early to detect if this is the admin store
        $path = Mage::app()->getRequest()->getPathInfo();
        if (substr($path, 0, 10) === '/_profiler') {
            return;
        }
        //this wont work if you use a custom url and we cant tell by now 
        //which one is configured. even if we read the local.xml manually it can still be set
        //in the database, so for now 80/20 solution :)
        if (substr($path, 0, 6) === '/admin') {
            return;
        }

        $this->getProfiler()->init();
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
            $url = Mage::getUrl('_profiler/index/panel', ['token' => $token]);
            $response->setHeader('X-Debug-Token-Link', $url);
        }

        $this->injectToolbar($response, $request, $token);
    }

    public function onTerminate(Varien_Event_Observer $observer)
    {
        foreach ($this->profiles as $request) {
            $this->profiler->saveProfile($this->profiles[$request]);
        }
    }

    /**
     * Injects the web debug toolbar into the given Response.
     *
     * @param Mage_Core_Controller_Response_Http $response A Response instance
     * @param Mage_Core_Controller_Request_Http  $request
     */
    protected function injectToolbar(Mage_Core_Controller_Response_Http $response, Mage_Core_Controller_Request_Http $request, $token = null)
    {
        $content = $response->getBody();
        $pos     = strripos($content, '</body>');

        if (false !== $pos) {
            $layout = Mage::app()->getLayout();
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
}