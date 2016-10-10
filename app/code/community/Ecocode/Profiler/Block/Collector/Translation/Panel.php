<?php

/**
 * Class Ecocode_Profiler_Block_Collector_Translation_Panel
 */
class Ecocode_Profiler_Block_Collector_Translation_Panel
    extends Ecocode_Profiler_Block_Collector_Base
{


    public function getMessageGroups()
    {
        /** @var Ecocode_Profiler_Model_Collector_TranslationDataCollector $collector */
        $collector = $this->getCollector();
        $statusCounts = $collector->getStateCount();

        $groups = array_fill_keys(array_keys($statusCounts), []);

        foreach ($collector->getTranslations() as $message) {
            $groups[$message['state']][] = $message;
        }

        return $groups;
    }

    public function renderTable(array $messages)
    {
        $tableBlock = $this->getLayout()->createBlock('core/template');
        $tableBlock->setTemplate('ecocode_profiler/collector/translation/panel/table.phtml');
        $tableBlock->setData('messages', $messages);
        return $tableBlock->toHtml();
    }
}
