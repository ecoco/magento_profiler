<?php

class Ecocode_Profiler_IndexController extends Ecocode_Profiler_Controller_AbstractController
{
    public function indexAction()
    {
        $this->loadLayout('profiler_default');
        $this->renderLayout();
    }

    public function toolbarAction()
    {
        $token    = $this->getRequest()->getParam(Ecocode_Profiler_Model_Profiler::URL_TOKEN_PARAMETER);
        $profiler = $this->getProfiler();

        $profile = $profiler->loadProfile($token);
        Mage::register('current_profile', $profile);

        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function searchAction()
    {
        /** @var Mage_Core_Controller_Request_Http$request */
        $request  = $this->getRequest();

        $ip     = preg_replace('/[^:\d\.]/', '', $request->getParam('ip'));
        $method = $request->getParam('method');
        $url    = $request->getParam('url');
        $start  = $request->getParam('start');
        $end    = $request->getParam('end');
        $limit  = $request->getParam('limit');
        $token  = $request->getParam('_token');

        if ($session = Mage::getSingleton('ecocode_profiler/session')) {
            /** @var Ecocode_Profiler_Model_Session $session */
            $session->setData('search_ip', $ip);
            $session->setData('search_method', $method);
            $session->setData('search_url', $url);
            $session->setData('search_start', $start);
            $session->setData('search_end', $end);
            $session->setData('search_limit', $limit);
            $session->setData('search_token', $token);
        }

        if (!empty($token)) {
            return $this->_redirect('_profiler/index/panel', [Ecocode_Profiler_Model_Profiler::URL_TOKEN_PARAMETER => $token]);
        }

        return $this->_redirect('_profiler/index/searchResults',
            [
                'ip'     => $ip,
                'method' => $method,
                'url'    => $url,
                'start'  => $start,
                'end'    => $end,
                'limit'  => $limit,
            ]
        );
    }

    public function searchResultsAction()
    {
        /** @var Mage_Core_Controller_Request_Http$request */
        $request  = $this->getRequest();
        $profiler = $this->getProfiler();

        $ip         = $request->getParam('ip');
        $method     = $request->getParam('method');
        $statusCode = $request->getParam('status_code');
        $url        = $request->getParam('url');
        $start      = $request->getParam('start');
        $end        = $request->getParam('end');
        $limit      = $request->getParam('limit');

        $data = [
            'request'     => $request,
            'tokens'      => $profiler->find($ip, $url, $limit, $method, $start, $end),
            'ip'          => $ip,
            'method'      => $method,
            'status_code' => $statusCode,
            'url'         => $url,
            'start'       => $start,
            'end'         => $end,
            'limit'       => $limit,
            'panel'       => null,
        ];

        $this->loadLayout('profiler_default');

        $this
            ->getLayout()
            ->getBlock('profiler.search.results')
            ->addData($data);

        $this->renderLayout();
    }

    public function panelAction()
    {
        $profiler = $this->getProfiler();

        /** @var Mage_Core_Controller_Request_Http $request */
        $request = $this->getRequest();

        $token = $request->getParam(Ecocode_Profiler_Model_Profiler::URL_TOKEN_PARAMETER);
        if (!$token) {
            return $this->_redirect('_profiler');
        }


        $panel = $request->getParam('panel', 'request');

        if ('latest' === $token && $latest = current($profiler->find(null, null, 1, null, null, null))) {
            $token = $latest['token'];
        }

        if (!$profile = $this->profiler->loadProfile($token)) {
            return $this->norouteAction();
        }

        /** @var Ecocode_Profiler_Model_Profile $profile */
        if (!$profile->hasCollector($panel)) {
            throw new Exception(sprintf('Panel "%s" is not available for token "%s".', $panel, $token));
        }

        Mage::register('current_profile', $profile);
        Mage::register('current_panel', $panel);

        $collector = $profile->getCollector($panel);

        $layoutHandler = 'collector_' . $collector->getName();
        $this->loadLayout(['profiler_default', $layoutHandler]);

        $panelBlock = $this->getLayout()->getBlock('panel');

        /** @var Ecocode_Profiler_Model_Profile $profile */
        if (!$panelBlock) {
            throw new Exception(sprintf('Panel Block for "%s" is not available for token "%s".', $panel, $token));
        }
        if (!($panelBlock instanceof Ecocode_Profiler_Block_Collector_Base)) {
            throw new Exception(sprintf('Panel Block must extend "Ecocode_Profiler_Block_Collector_AbstractBase"'));
        }

        $panelBlock->setCollector($collector);

        return $this->renderLayout();
    }

    /**
     * @codeCoverageIgnore
     */
    public function phpinfoAction()
    {
        phpinfo();
    }
}
