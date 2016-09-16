<?php

class Ecocode_Profiler_Model_Collector_ConfigDataCollector
    extends Ecocode_Profiler_Model_Collector_AbstractDataCollector
{

    /**
     * {@inheritdoc}
     */
    public function collect(Mage_Core_Controller_Request_Http $request, Mage_Core_Controller_Response_Http $response, \Exception $exception = null)
    {
        $store   = Mage::app()->getStore();
        $website = Mage::app()->getWebsite();

        $rewriteHelper = Mage::helper('ecocode_profiler/rewrite');
        $this->data    = [
            'store_id'                 => $store->getId(),
            'store_name'               => $store->getName(),
            'store_code'               => $store->getCode(),
            'website_id'               => $website->getId(),
            'website_name'             => $website->getName(),
            'website_code'             => $website->getCode(),
            'developer_mode'           => Mage::getIsDeveloperMode(),
            'token'                    => $this->retrieveToken($response),
            'magento_version'          => Mage::getVersion(),
            'magento_modules'          => $this->collectMagentoModules(),
            'module_rewrites'          => $rewriteHelper->getRewrites(),
            'module_rewrite_conflicts' => $rewriteHelper->getRewriteConflicts(),
            'symfony_state'            => 'unknown',
            'php_version'              => PHP_VERSION,
            'xdebug_enabled'           => extension_loaded('xdebug'),
            'eaccel_enabled'           => extension_loaded('eaccelerator') && ini_get('eaccelerator.enable'),
            'apc_enabled'              => extension_loaded('apc') && ini_get('apc.enabled'),
            'xcache_enabled'           => extension_loaded('xcache') && ini_get('xcache.cacher'),
            'wincache_enabled'         => extension_loaded('wincache') && ini_get('wincache.ocenabled'),
            'zend_opcache_enabled'     => extension_loaded('Zend OPcache') && ini_get('opcache.enable'),
            'sapi_name'                => PHP_SAPI,
        ];

    }

    protected function collectMagentoModules()
    {
        /** @var Mage_Core_Model_Config_Element $modules */
        $moduleList = Mage::getConfig()->getNode('modules')->children();
        $modules    = [];
        foreach ($moduleList as $key => $node) {
            $data           = $node->asArray();
            $data['active'] = $data['active'] === 'true';
            $modules[$key]  = $data;
        }

        return $modules;
    }

    public function getMagentoVersion()
    {
        return $this->data['magento_version'];
    }


    public function getStoreName()
    {
        return $this->data['store_name'];
    }

    public function getStoreCode()
    {
        return $this->data['store_code'];
    }

    public function getWebsiteName()
    {
        return $this->data['website_name'];
    }

    public function getWebsiteCode()
    {
        return $this->data['website_code'];
    }

    /**
     * Gets the token.
     *
     * @return string The token
     */
    public function getToken()
    {
        return $this->data['token'];
    }

    /**
     * Gets the PHP version.
     *
     * @return string The PHP version
     */
    public function getPhpVersion()
    {
        return $this->data['php_version'];
    }

    /**
     * Returns true if the developer mode is enabled.
     *
     * @return bool true if debug is enabled, false otherwise
     */
    public function isDeveloperMode()
    {
        return $this->data['developer_mode'];
    }

    public function getMagentoModules()
    {
        return $this->data['magento_modules'];
    }

    /**
     * @param $state
     * @return array
     */
    public function geModulesByState($state)
    {
        return array_filter($this->getMagentoModules(), function ($module) use ($state) {
            return $module['active'] === $state;
        });
    }

    /**
     * Returns true if the XDebug is enabled.
     *
     * @return bool true if XDebug is enabled, false otherwise
     */
    public function hasXDebug()
    {
        return $this->data['xdebug_enabled'];
    }

    /**
     * Returns true if EAccelerator is enabled.
     *
     * @return bool true if EAccelerator is enabled, false otherwise
     */
    public function hasEAccelerator()
    {
        return $this->data['eaccel_enabled'];
    }

    /**
     * Returns true if APC is enabled.
     *
     * @return bool true if APC is enabled, false otherwise
     */
    public function hasApc()
    {
        return $this->data['apc_enabled'];
    }

    /**
     * Returns true if Zend OPcache is enabled.
     *
     * @return bool true if Zend OPcache is enabled, false otherwise
     */
    public function hasZendOpcache()
    {
        return $this->data['zend_opcache_enabled'];
    }

    /**
     * Returns true if XCache is enabled.
     *
     * @return bool true if XCache is enabled, false otherwise
     */
    public function hasXCache()
    {
        return $this->data['xcache_enabled'];
    }

    /**
     * Returns true if WinCache is enabled.
     *
     * @return bool true if WinCache is enabled, false otherwise
     */
    public function hasWinCache()
    {
        return $this->data['wincache_enabled'];
    }

    /**
     * Returns true if any accelerator is enabled.
     *
     * @return bool true if any accelerator is enabled, false otherwise
     */
    public function hasAccelerator()
    {
        return $this->hasApc() || $this->hasZendOpcache() || $this->hasEAccelerator() || $this->hasXCache() || $this->hasWinCache();
    }

    /**
     * Gets the PHP SAPI name.
     *
     * @return string The environment
     */
    public function getSapiName()
    {
        return $this->data['sapi_name'];
    }

    public function retrieveToken(Mage_Core_Controller_Response_Http $response)
    {
        $token = null;
        foreach ($response->getHeaders() as $header) {
            if ($header['name'] === 'X-Debug-Token') {
                $token = $header['value'];
                break;
            }
        }
        return $token;
    }

    public function getName()
    {
        return 'config';
    }

}