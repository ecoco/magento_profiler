<?php

Ecocode_Profiler_Helper_Data::loadRenamedClass('core/Mage/Core/Model/Store.php', 'Original_Mage_Core_Model_Store');


class Mage_Core_Model_Store extends
    Original_Mage_Core_Model_Store
{
    protected $_isDev;

    /**
     * Remove script file name from url in case when server rewrites are enabled
     *
     * @SuppressWarnings("superglobals")
     * @param   string $url
     * @return  string
     */
    protected function _updatePathUseRewrites($url)
    {
        $script = $this->isDev() ? 'dev.php' : 'index.php';
        if ($this->isAdmin() || $this->isDev() || !$this->getConfig(self::XML_PATH_USE_REWRITES) || !Mage::isInstalled()) {
            $indexFileName = $this->_isCustomEntryPoint() ? $script : basename($_SERVER['SCRIPT_FILENAME']);
            $url .= $indexFileName . '/';
        }
        return $url;
    }

    protected function isDev()
    {
        if ($this->_isDev === null) {
            $this->_isDev = Mage::app() instanceof Ecocode_Profiler_Model_AppDev;
        }

        return $this->_isDev;
    }
}
