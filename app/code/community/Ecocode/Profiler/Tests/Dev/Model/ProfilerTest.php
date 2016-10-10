<?php


class Ecocode_Profiler_Tests_Dev_Model_ProfilerTest extends TestHelper
{
    private $tmp;
    /** @var Ecocode_Profiler_Model_Profiler_FileStorage */
    private $storage;

    public function testFindWorksWithDates()
    {
        $profiler = new Ecocode_Profiler_Model_Profiler($this->storage);

        $this->assertCount(0, $profiler->find(null, null, null, null, '7th April 2014', '9th April 2014'));
    }

    public function testFindWorksWithTimestamps()
    {
        $profiler = new Ecocode_Profiler_Model_Profiler($this->storage);

        $this->assertCount(0, $profiler->find(null, null, null, null, '1396828800', '1397001600'));
    }

    public function testFindWorksWithInvalidDates()
    {
        $profiler = new Ecocode_Profiler_Model_Profiler($this->storage);

        $this->assertCount(0, $profiler->find(null, null, null, null, 'some string', ''));
    }

    public function testFindWorksWithStatusCode()
    {
        $profiler = new Ecocode_Profiler_Model_Profiler($this->storage);

        $this->assertCount(0, $profiler->find(null, null, null, null, null, null, '204'));
    }

    protected function setUp()
    {
        $this->tmp = tempnam(sys_get_temp_dir(), 'sf2_profiler');
        if (file_exists($this->tmp)) {
            @unlink($this->tmp);
        }

        $this->storage = new Ecocode_Profiler_Model_Profiler_FileStorage(['dsn' => 'file:' . $this->tmp]);
        $this->storage->purge();
    }

    protected function tearDown()
    {
        if (null !== $this->storage) {
            $this->storage->purge();
            $this->storage = null;

            @unlink($this->tmp);
        }
    }
}
