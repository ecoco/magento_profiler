<?php


class Ecocode_Profiler_Tests_Dev_Helper_SqlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Ecocode_Profiler_Helper_Sql
     */
    private $sql;

    protected function setUp()
    {
        $this->sql = new Ecocode_Profiler_Helper_Sql();
    }

    public function testReplaceQueryParametersWithPostgresCasting()
    {
        $query      = 'a=? OR (1)::string OR b=?';
        $parameters = [1, 2];
        $result     = $this->sql->replaceQueryParameters($query, $parameters);
        $this->assertEquals('a=1 OR (1)::string OR b=2', $result);
    }

    public function testReplaceQueryParametersWithStartingIndexAtOne()
    {
        $query      = 'a=? OR b=?';
        $parameters = [
            1 => 1,
            2 => 2
        ];
        $result     = $this->sql->replaceQueryParameters($query, $parameters);
        $this->assertEquals('a=1 OR b=2', $result);
    }

    public function testReplaceQueryParameters()
    {
        $query      = 'a=? OR b=?';
        $parameters = [
            1,
            2
        ];
        $result     = $this->sql->replaceQueryParameters($query, $parameters);
        $this->assertEquals('a=1 OR b=2', $result);
    }

    public function testReplaceQueryParametersWithNamedIndex()
    {
        $query      = 'a=:a OR b=:b';
        $parameters = [
            'a' => 1,
            'b' => 2
        ];
        $result     = $this->sql->replaceQueryParameters($query, $parameters);
        $this->assertEquals('a=1 OR b=2', $result);
    }
}
