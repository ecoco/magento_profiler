<?php

/**
 * Class Ecocode_Profiler_Model_Core_Config
 */
class Ecocode_Profiler_Model_Core_Config extends Mage_Core_Model_Config
{

    public function loadBase()
    {
        parent::loadBase();

        //needed as the mysql collector needs to log queries before the config is loaded!!!
        $this->setNode('global/models/ecocode_profiler/class', 'Ecocode_Profiler_Model');
        $this->setNode('global/helpers/ecocode_profiler/class', 'Ecocode_Profiler_Helper');
    }
    /**
     * Load modules configuration
     *
     * @return Mage_Core_Model_Config
     */
    public function loadModules()
    {
        parent::loadModules();

        /* load development.xml for all modules if present */
        $this->loadModulesConfiguration(['development.xml'], $this);
        return $this;
    }

    public function loadDb()
    {
        parent::loadDb();

        //overwrite symlinks if needed
        $this->enableSymlinks();

        return $this;
    }

    protected function enableSymlinks()
    {
        $dir = $this->getModuleDir('etc', 'Ecocode_Profiler');
        if (is_link($dir)) {
            //due to magentos awesome "config->loadDb()" call we need to overwrite each store
            //as the config gets copied over into all stores, so setting only the "default" is not enough
            $this->setNode('default/dev/template/allow_symlink', true);
            foreach($this->getNode('websites')->children() as $website) {
                $website->setNode(Mage_Core_Block_Template::XML_PATH_TEMPLATE_ALLOW_SYMLINK, true);
            }
            foreach($this->getNode('stores')->children() as $store) {
                $store->setNode(Mage_Core_Block_Template::XML_PATH_TEMPLATE_ALLOW_SYMLINK, true);
            }
        }
    }
}
