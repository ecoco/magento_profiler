<?php

class Ecocode_Profiler_Tests_Dev_Model_Collector_TranslationDataCollectorTest
    extends TestHelper
{

    public function testCollect()
    {
        $translate = new Mage_Core_Model_Translate();
        /** @var Ecocode_Profiler_Model_Collector_TranslationDataCollector $collector */
        $collector = $this->getMockBuilder('Ecocode_Profiler_Model_Collector_TranslationDataCollector')
            ->setMethods(['getTranslator'])
            ->getMock();

        $collector->method('getTranslator')->willReturn($translate);

        $messageLog = [
            ['DE_de', 'code-1', 'the-text', 'the-translation', 'translated'],
            ['DE_de', 'code-1', 'the-text', 'the-translation', 'translated'],
            ['DE_de', 'code-2', 'the-text', 'the-translation', 'missing'],
            ['DE_de', 'code-3', 'the-text', 'the-translation', 'invalid'],
            ['DE_de', 'with-trace', 'the-text', 'the-translation', 'fallback', [], null, ['trace1' => []]],
            ['DE_de', 'with-trace', 'the-text', 'the-translation', 'fallback', [], null, ['trace2' => []]],
            ['DE_de', 'multiple-parameters', 'the-text', 'the-translation', 'fallback', ['a' => 'b'], 'catalog'],
            ['DE_de', 'multiple-parameters', 'the-text', 'the-translation', 'fallback', ['c' => 'd'], 'catalog']
        ];

        $messages = [];
        foreach ($messageLog as $message) {
            $messages[] = [
                'locale'      => $message[0],
                'code'        => $message[1],
                'text'        => $message[2],
                'translation' => $message[3],
                'state'       => $message[4],
                'parameters'  => isset($message[5]) ? $message[5] : [],
                'module'      => isset($message[6]) ? $message[6] : null,
                'trace'       => isset($message[7]) ? $message[7] : [],
            ];
        }
        $messagesProperty = new ReflectionProperty('Mage_Core_Model_Translate', 'messages');
        $messagesProperty->setAccessible(true);
        $messagesProperty->setValue($translate, $messages);

        $collector->collect(
            new Mage_Core_Controller_Request_Http(),
            new Mage_Core_Controller_Response_Http()
        );

        $translations = $collector->getTranslations();
        $this->assertCount(5, $translations);
        $this->assertEquals(5, $collector->getTranslationCount());

        $this->assertEquals(2, $translations['code-1']['count']);
        $this->assertEquals(1, $translations['code-2']['count']);

        //test stats counts

        //test trace set
        $this->assertCount(2, $translations['with-trace']['traces']);

        //test parameters
        $this->assertEmpty($translations['code-1']['parameters']);
        $this->assertNotEmpty($translations['multiple-parameters']['parameters']);
        $this->assertEquals([['a' => 'b'], ['c' => 'd']], $translations['multiple-parameters']['parameters']);

        //getStateCount
        $this->assertTrue(is_array($collector->getStateCount()));
        $this->assertEquals(1, $collector->getStateCount('translated'));
        $this->assertEquals(1, $collector->getStateCount('missing'));
        $this->assertEquals(1, $collector->getStateCount('invalid'));
        $this->assertEquals(2, $collector->getStateCount('fallback'));

        $this->assertEquals(4, $collector->getNotOkCount());


        return $collector;
    }
}
