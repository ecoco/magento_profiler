<?php

class Ecocode_Profiler_Model_Collector_TranslationDataCollector
    extends Ecocode_Profiler_Model_Collector_AbstractDataCollector
{
    protected $_currentBlock;

    protected $translations = [];

    protected $stateCounts = [
        'translated' => 0,
        'missing'    => 0,
        'invalid'    => 0,
        'fallback'   => 0
    ];

    /**
     * @param       $locale
     * @param       $code
     * @param       $text
     * @param       $translation
     * @param       $state
     * @param array $parameters
     * @param null  $module
     */
    public function logTranslation(
        $locale,
        $code,
        $text,
        $translation,
        $state,
        $parameters = [],
        $module = null
    )
    {
        $this->translations[] = [
            'locale'      => $locale,
            'code'        => $code,
            'text'        => $text,
            'translation' => $translation,
            'state'       => $state,
            'parameters'  => $parameters,
            'module'      => $module
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function collect(Mage_Core_Controller_Request_Http $request, Mage_Core_Controller_Response_Http $response, \Exception $exception = null)
    {

        $this->data['state_counts'] = $this->stateCounts;
        $translations               = [];

        foreach ($this->translations as $translation) {
            $translationId = $translation['code'];
            if (!isset($translations[$translationId])) {
                $translation['count']         = 1;
                $translation['parameters']    = !empty($translation['parameters']) ? [$translation['parameters']] : [];
                $translations[$translationId] = $translation;
                $this->data['state_counts'][$translation['state']]++;
            } else {
                if (!empty($translation['parameters'])) {
                    $translation[$translationId]['parameters'][] = $translation['parameters'];
                }
                $translations[$translationId]['count']++;
            }
        }
        $this->data['translations']      = $translations;
        $this->data['translation_count'] = count($translations);
    }

    public function getTranslations()
    {
        return $this->data['translations'];
    }

    public function getTranslationCount()
    {
        return $this->data['translation_count'];
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
     */
    public function getName()
    {
        return 'translation';
    }
}