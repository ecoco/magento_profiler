<?php

Ecocode_Profiler_Helper_Data::loadRenamedClass('core/Mage/Core/Model/Translate.php', 'Original_Mage_Core_Model_Translate');

/**
 * @author ecocode GmbH <jk@ecocode.de>
 * @author Justus Krapp <jk@ecocode.de>
 */
class Mage_Core_Model_Translate extends Original_Mage_Core_Model_Translate
{

    const STATE_TRANSLATED  = 'translated';
    const STATE_FALLBACK    = 'fallback';
    const STATE_MISSING     = 'missing';
    const STATE_INVALID     = 'invalid';

    protected $profilerConfig = null;

    protected $currentMessage = null;

    protected $messages = [];

    protected $source  = [];

    protected $sourceMap = [
        '_loadModuleTranslation' => 'module',
        '_loadThemeTranslation'  => 'theme',
        '_loadDbTranslation'     => 'db',
    ];


    public function init($area, $forceReload = false)
    {
        parent::init($area, $forceReload);
    }

    protected function _loadCache()
    {
        if (!$this->_canUseCache()) {
            return false;
        }

        $cacheData = Mage::app()->loadCache($this->getCacheId());
        if ($cacheData === false) {
            return false;
        }

        $cacheData = unserialize($cacheData);
        if (!isset($cacheData['source'])) {
            return false;
        }

        $this->source = $cacheData['source'];
        return $cacheData['data'];
    }

    /**
     * Saving data cache
     *
     * @param   string $area
     * @return  Mage_Core_Model_Translate
     */
    protected function _saveCache()
    {
        if (!$this->_canUseCache()) {
            return $this;
        }
        $data = [
            'data'   => $this->getData(),
            'source' => $this->source
        ];
        Mage::app()->saveCache(serialize($data), $this->getCacheId(), [self::CACHE_TAG], null);
        return $this;
    }


    public function translate($args)
    {
        $argsCopy             = $args;
        $this->currentMessage = [
            'locale' => $this->_locale,
            'module' => null,
            'trace'  => []
        ];

        $text = array_shift($argsCopy);

        if ($text instanceof Mage_Core_Model_Translate_Expr) {
            $this->currentMessage['module'] = $text->getModule();
        }

        $translation = parent::translate($args);
        if ($translation === '') {
            //just return nothing we can do here? maybe log a stacktrace?
            return $translation;
        }

        if (@vsprintf($this->currentMessage['translation'], $argsCopy) === false) {
            $trace = $this->addTrace();
            if ($trace && $this->traceHasFunctionCall($trace, 'getTranslateJson')) {
                //dont log invalid as strings are used with empty placeholders is intended here
            } else {
                $this->currentMessage['state'] = self::STATE_INVALID;
            }
        }

        $this->currentMessage['parameters']  = $argsCopy;
        $this->currentMessage['translation'] = $translation;

        $this->log();
        return $translation;
    }

    public function _getTranslatedString($text, $code)
    {
        $this->currentMessage['text'] = $text;
        $this->currentMessage['code'] = $code;
        $source                       = null;

        $translated = parent::_getTranslatedString($text, $code);
        if (array_key_exists($code, $this->_data)) {
            $state  = self::STATE_TRANSLATED;
            $source = $this->source[$code];
        } elseif (array_key_exists($text, $this->_data)) {
            $source = $this->source[$text];
            $state  = self::STATE_FALLBACK;
            $this->addTrace();
        } else {
            $state = self::STATE_MISSING;
            $this->addTrace();
        }

        $this->currentMessage['state']       = $state;
        $this->currentMessage['translation'] = $translated;
        $this->currentMessage['source']      = $source;

        return $translated;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    protected function log()
    {
        $this->messages[] = [
            'locale'      => $this->currentMessage['locale'],
            'code'        => $this->currentMessage['code'],
            'text'        => $this->currentMessage['text'],
            'translation' => $this->currentMessage['translation'],
            'state'       => $this->currentMessage['state'],
            'parameters'  => $this->currentMessage['parameters'],
            'module'      => $this->currentMessage['module'],
            'source'      => $this->currentMessage['source'],
            'trace'       => $this->currentMessage['trace']
        ];
    }

    /**
     * Adding translation data
     *
     * @param array  $data
     * @param string $scope
     * @param bool   $forceReload
     * @return Mage_Core_Model_Translate
     */
    protected function _addData($data, $scope, $forceReload = false)
    {
        $source = debug_backtrace()[1]['function'];
        if (isset($this->sourceMap[$source])) {
            $source = $this->sourceMap[$source];
        }

        foreach ($data as $key => $value) {
            $key   = $this->_prepareDataString($key);
            $value = $this->_prepareDataString($value);

            $scopeKey = $key;
            $sourceName = $source;
            if ($scope) {
                $scopeKey = $scope . self::SCOPE_SEPARATOR . $key;
                $sourceName .= ' (' . $scope . ')';
            } else {
                // we have no scope key so this is coming from translate.csv or db in this case magento overwrites
                // the translation in any case
            }

            $this->_data[$scopeKey]  = $value;
            $this->source[$scopeKey] = $sourceName;
            if (!isset($this->_data[$key]) && !Mage::getIsDeveloperMode()) {
                $this->_data[$key]  = $value;
                $this->source[$key] = $sourceName;
            }
            continue;
        }
        return $this;
    }

    /**
     * @return array
     */
    protected function addTrace()
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        while (($trace = reset($backtrace)) && (!isset($trace['function']) || $trace['function'] !== '__')) {
            array_shift($backtrace);
        }

        return $this->currentMessage['trace'] = array_slice($backtrace, 0, $this->getConfigValue('backtrace_limit', 10));
    }

    /**
     * @param array $trace
     * @param       $functionName
     * @return bool
     */
    protected function traceHasFunctionCall(array $trace, $functionName)
    {
        foreach ($trace as $item) {
            if (isset($item['function']) && $item['function'] === $functionName) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param      $key
     * @param null $default
     * @return mixed
     */
    protected function getConfigValue($key, $default = null)
    {
        return $this->getProfilerConfig()->getValue($key, $default);
    }

    /**
     * @return Ecocode_Profiler_Model_Config
     *
     * @codeCoverageIgnore
     */
    protected function getProfilerConfig()
    {
        if ($this->profilerConfig === null) {
            $this->profilerConfig = Mage::getSingleton('ecocode_profiler/config');
        }

        return $this->profilerConfig;
    }
}
