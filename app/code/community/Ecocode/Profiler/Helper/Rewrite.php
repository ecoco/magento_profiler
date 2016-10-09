<?php

/**
 * Class Ecocode_Profiler_Helper_Rewrite
 *
 * taken from the awesome b98-magerun:
 * https://github.com/netz98/n98-magerun/blob/master/src/N98/Magento/Command/Developer/Module/Rewrite/ConflictsCommand.php#L49-L55
 */
class Ecocode_Profiler_Helper_Rewrite
{
    protected $_rewrites     = null;
    protected $_rewriteTypes = [
        'blocks',
        'helpers',
        'models',
    ];

    /**
     * @codeCoverageIgnore
     * @return SimpleXMLElement
     */
    public function getModules()
    {
        return Mage::getConfig()->getNode('modules')->children();
    }

    /**
     * @codeCoverageIgnore
     * @param $moduleName
     * @param $file
     * @return bool|SimpleXMLElement
     */
    public function getModuleConfigXml($moduleName, $file)
    {
        $file = Mage::getConfig()->getModuleDir('etc', $moduleName) . DIRECTORY_SEPARATOR . $file;
        if (!is_readable($file)) {
            return false;
        }

        $xml = \simplexml_load_file($file);
        if (!$xml) {
            return false;
        }

        return $xml;
    }

    /**
     * Return all rewrites
     *
     * @return array
     */
    public function loadRewrites()
    {
        if ($this->_rewrites === null) {
            $files     = ['config.xml', 'development.xml'];
            $prototype = $this->_rewriteTypes;
            $return    = array_combine($prototype, array_fill(0, count($prototype), []));
            // Load config of each module because modules can overwrite config each other. Global config is already merged
            $modules = $this->getModules();
            foreach ($modules as $moduleName => $moduleData) {
                // Check only active modules
                if (!$moduleData->is('active')) {
                    continue;
                }
                // Load config of module
                foreach ($files as $file) {
                    $xml = $this->getModuleConfigXml($moduleName, $file);
                    if (!$xml) {
                        continue;
                    }
                    $rewriteElements = $xml->xpath('//*/*/rewrite');
                    foreach ($rewriteElements as $element) {
                        $type = \simplexml_import_dom(dom_import_simplexml($element)->parentNode->parentNode)->getName();
                        if (!isset($return[$type])) {
                            continue;
                        }
                        foreach ($element->children() as $child) {
                            $groupClassName = \simplexml_import_dom(dom_import_simplexml($element)->parentNode)->getName();
                            if (!isset($return[$type][$groupClassName . '/' . $child->getName()])) {
                                $return[$type][$groupClassName . '/' . $child->getName()] = [];
                            }
                            $return[$type][$groupClassName . '/' . $child->getName()][] = (string)$child;
                        }
                    }
                }
            }
            $this->_rewrites = $return;
        }

        return $this->_rewrites;
    }

    public function getRewriteConflicts()
    {
        $conflicts = [];
        $rewrites  = $this->loadRewrites();
        foreach ($rewrites as $type => $data) {
            if (!is_array($data)) {
                continue;
            }
            foreach ($data as $class => $rewriteClasses) {
                if (!$this->_isInheritanceConflict($rewriteClasses)) {
                    continue;
                }
                $conflicts[] = [
                    'type'         => $type,
                    'class'        => $class,
                    'rewrites'     => $rewriteClasses,
                    'loaded_class' => $this->_getLoadedClass($type, $class),
                ];
            }
        }
        return $conflicts;
    }


    /**
     * Check if rewritten class has inherited the parent class.
     * If yes we have no conflict. The top class can extend every core class.
     * So we cannot check this.
     *
     * @var array $classes
     * @return bool
     */
    protected function _isInheritanceConflict($classes)
    {
        $count   = count($classes);
        $classes = array_reverse($classes);
        for ($i = 1; $i < $count; $i++) {
            try {
                if (class_exists($classes[$i - 1])
                    && class_exists($classes[$i])
                ) {
                    if (!is_a($classes[$i - 1], $classes[$i], true)) {
                        return true;
                    }
                }
            } catch (Exception $e) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns loaded class by type like models or blocks
     *
     * @param string $type
     * @param string $class
     * @return string
     */
    protected function _getLoadedClass($type, $class)
    {
        switch ($type) {
            case 'blocks':
                return Mage::getConfig()->getBlockClassName($class);
            case 'helpers':
                return Mage::getConfig()->getHelperClassName($class);
        }
        return Mage::getConfig()->getModelClassName($class);
    }
}
