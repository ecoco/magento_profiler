<?php

class Ecocode_Profiler_Tests_Dev_Block_Collector_Mysql_PanelTest
    extends TestHelper
{
    public function testGetQueryType()
    {
        $panel  = new Ecocode_Profiler_Block_Collector_Mysql_Panel();
        $method = $this->getProtectedMethod(
            'Ecocode_Profiler_Block_Collector_Mysql_Panel',
            'getQueryType'
        );

        $this->assertEquals(
            'select',
            $method->invoke($panel, 'SELECT * FROM test_table')
        );
    }

    public function getQueryIdDataProvider()
    {
        return [
            [['sql' => 'SELECT * FROM test', 'params' => []], 'a14cbe1d40e1a7cf457082ac1f816357'],
            [['sql' => 'SELECT * FROM test'], 'a14cbe1d40e1a7cf457082ac1f816357'],
            [
                ['sql'    => 'SELECT `core_url_rewrite`.* FROM `core_url_rewrite` WHERE (request_path IN (:path0, :path1)) AND (store_id IN(0, 1))',
                 'params' => [':path0' => '', ':path1' => '/']],
                '0c4683ec0763bab8577156aff6cbc670'
            ],
            [
                ['sql'    => 'SELECT `core_url_rewrite`.* FROM `core_url_rewrite` WHERE (request_path IN (:path0, :path1)) AND (store_id IN(0, 1))',
                 'params' => [':path0' => '', ':path1' => '/test']],
                '7bad4bab9bb6259946031ef8b32baea4'
            ]
        ];
    }

    /**
     * @param $queryData
     * @param $expectId
     *
     * @dataProvider getQueryIdDataProvider
     */
    public function testGetQueryId($queryData, $expectId)
    {
        $panel  = new Ecocode_Profiler_Block_Collector_Mysql_Panel();
        $method = $this->getProtectedMethod(
            'Ecocode_Profiler_Block_Collector_Mysql_Panel',
            'getQueryId'
        );

        $id = $method->invoke($panel, $queryData);
        $this->assertEquals($expectId, $id);
    }

    public function testProcessIdentical()
    {
        $panel  = new Ecocode_Profiler_Block_Collector_Mysql_Panel();
        $method = $this->getProtectedMethod(
            'Ecocode_Profiler_Block_Collector_Mysql_Panel',
            'processIdentical'
        );

        $method->invoke($panel, ['sql' => 'query1', 'time' => 1, 'trace' => ['1.1']]);
        $method->invoke($panel, ['sql' => 'query1', 'time' => 2, 'trace' => ['1.2']]);
        $method->invoke($panel, ['sql' => 'query2', 'time' => 4, 'trace' => ['2']]);
        $queries = $panel->getIdenticalQueries();


        $this->assertEquals([
            '8b09fc98eb98edcff9700ee747064cd6' => [
                'count'      => 2,
                'id'         => '8b09fc98eb98edcff9700ee747064cd6',
                'total_time' => 3,
                'traces'     => [['1.1'], ['1.2']],
                'query'      => ['sql' => 'query1'],
            ],
            '796e92b9df3e0fc7b9ee0c37a462cec8' => [
                'count'      => 1,
                'id'         => '796e92b9df3e0fc7b9ee0c37a462cec8',
                'total_time' => 4,
                'traces'     => [['2']],
                'query'      => ['sql' => 'query2'],
            ]
        ], $queries);
    }

    public function testProcessContext()
    {
        $panel  = new Ecocode_Profiler_Block_Collector_Mysql_Panel();
        $method = $this->getProtectedMethod(
            'Ecocode_Profiler_Block_Collector_Mysql_Panel',
            'processContext'
        );

        $method->invoke($panel, ['id' => 'query1', 'context' => 'context1', 'time' => 1]);
        $method->invoke($panel, ['id' => 'query2', 'context' => 'context1', 'time' => 2]);
        $method->invoke($panel, ['id' => 'query3', 'context' => 'context2', 'time' => 4]);
        $queries = $panel->getByContext();


        $this->assertEquals([
            'context1' => [
                'count'      => 2,
                'total_time' => 3,
                'queries'    => [
                    ['id' => 'query1', 'context' => 'context1', 'time' => 1],
                    ['id' => 'query2', 'context' => 'context1', 'time' => 2,]
                ],
                'name'       => 'context1',
            ],
            'context2' => [
                'count'      => 1,
                'total_time' => 4,
                'name'       => 'context2',
                'queries'    => [['id' => 'query3', 'context' => 'context2', 'time' => 4]],
            ]
        ], $queries);
    }

}
