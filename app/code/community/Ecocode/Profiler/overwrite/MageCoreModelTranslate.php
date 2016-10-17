<?php

Ecocode_Profiler_Helper_Data::loadRenamedClass('core/Mage/Core/Model/Translate.php', 'Original_Mage_Core_Model_Translate');

/**
 * @author ecocode GmbH <jk@ecocode.de>
 * @author Justus Krapp <jk@ecocode.de>
 */
class Mage_Core_Model_Translate extends Original_Mage_Core_Model_Translate
{
    const STATE_TRANSLATED = 'translated';
    const STATE_FALLBACK   = 'fallback';
    const STATE_MISSING    = 'missing';
    const STATE_INVALID    = 'invalid';

    protected $currentMessage = null;

    protected $messages = [];


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

        $translated = parent::_getTranslatedString($text, $code);
        if (array_key_exists($code, $this->_data)) {
            $state = self::STATE_TRANSLATED;
        } elseif (array_key_exists($text, $this->_data)) {
            $state = self::STATE_FALLBACK;
            $this->addTrace();
        } else {
            $state = self::STATE_MISSING;
            $this->addTrace();
        }

        $this->currentMessage['state']       = $state;
        $this->currentMessage['translation'] = $translated;

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
        foreach ($data as $key => $value) {

            /*
            we needed to simplify this to properly detect not translated strings and their scope
            */
            $key   = $this->_prepareDataString($key);
            $value = $this->_prepareDataString($value);

            $scopeKey = $scope . self::SCOPE_SEPARATOR . $key;

            $this->_data[$scopeKey] = $value;
            if (!isset($this->_data[$key]) && !Mage::getIsDeveloperMode()) {
                $this->_data[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    protected function addTrace()
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 20);

        while (($trace = reset($backtrace)) && (!isset($trace['function']) || $trace['function'] !== '__')) {
            array_shift($backtrace);
        }

        return $this->currentMessage['trace'] = array_slice($backtrace, 0, 10);
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
}
