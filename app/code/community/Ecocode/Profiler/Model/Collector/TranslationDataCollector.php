<?php

/**
 * Class Ecocode_Profiler_Model_Collector_TranslationDataCollector
 */
class Ecocode_Profiler_Model_Collector_TranslationDataCollector
    extends Ecocode_Profiler_Model_Collector_AbstractDataCollector
{
    protected $_currentBlock;

    protected $stateCounts = [
        'translated' => 0,
        'missing'    => 0,
        'invalid'    => 0,
        'fallback'   => 0
    ];

    /**
     * @return Mage_Core_Model_Translate
     */
    protected function getTranslator()
    {
        return Mage::getSingleton('core/translate');
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Mage_Core_Controller_Request_Http $request, Mage_Core_Controller_Response_Http $response, \Exception $exception = null)
    {

        $this->data['state_counts'] = $this->stateCounts;
        $translations               = [];

        foreach ($this->getTranslator()->getMessages() as $translation) {
            $translationId = $translation['code'];
            if (!isset($translations[$translationId])) {
                $translation['count']         = 1;
                $translation['parameters']    = !empty($translation['parameters']) ? [$translation['parameters']] : [];
                $translation['traces']        = !empty($translation['trace']) ? [$translation['trace']] : [];
                unset($translation['trace']);
                $translations[$translationId] = $translation;
                $this->data['state_counts'][$translation['state']]++;
            } else {
                if (!empty($translation['parameters'])) {
                    $translations[$translationId]['parameters'][] = $translation['parameters'];
                }
                if (!empty($translation['trace'])) {
                    $translations[$translationId]['traces'][] = $translation['trace'];
                }
                $translations[$translationId]['count']++;
            }
        }
        $this->data['translations']      = $translations;
        $this->data['translation_count'] = count($translations);
    }

    public function getTranslations()
    {
        return $this->getData('translations', []);
    }

    public function getTranslationCount()
    {
        return $this->getData('translation_count', 0);
    }

    public function getStateCount($status = null)
    {
        if ($status === null) {
            return $this->data['state_counts'];
        }
        return $this->data['state_counts'][$status];
    }

    public function getNotOkCount()
    {
        return $this->getStateCount('invalid')
        + $this->getStateCount('missing')
        + $this->getStateCount('fallback');
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getName()
    {
        return 'translation';
    }
}
