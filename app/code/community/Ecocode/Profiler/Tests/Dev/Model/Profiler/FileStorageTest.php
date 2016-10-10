<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Ecocode_Profiler_Tests_Dev_Model_Profiler_FileStorageTest
    extends \TestHelper
{
    private $tmpDir;
    private $storage;

    protected function setUp()
    {
        $this->tmpDir = sys_get_temp_dir() . '/sf2_profiler_file_storage';
        if (is_dir($this->tmpDir)) {
            self::cleanDir();
        }
        $this->storage = new Ecocode_Profiler_Model_Profiler_FileStorage(['dsn' => 'file:' . $this->tmpDir]);
        $this->storage->purge();
    }

    protected function tearDown()
    {
        self::cleanDir();
    }

    public function testStore()
    {
        for ($i = 0; $i < 10; ++$i) {
            $profile = new Ecocode_Profiler_Model_Profile('token_' . $i);
            $profile->setIp('127.0.0.1');
            $profile->setUrl('http://foo.bar');
            $profile->setMethod('GET');
            $this->storage->write($profile);
        }
        $this->assertCount(10, $this->storage->find('127.0.0.1', 'http://foo.bar', 20, 'GET'), '->write() stores data in the storage');
    }

    public function testChildren()
    {
        $parentProfile = new Ecocode_Profiler_Model_Profile('token_parent');
        $parentProfile->setIp('127.0.0.1');
        $parentProfile->setUrl('http://foo.bar/parent');

        $childProfile = new Ecocode_Profiler_Model_Profile('token_child');
        $childProfile->setIp('127.0.0.1');
        $childProfile->setUrl('http://foo.bar/child');

        $parentProfile->addChild($childProfile);

        $this->storage->write($parentProfile);
        $this->storage->write($childProfile);

        // Load them from storage
        $parentProfile = $this->storage->read('token_parent');
        $childProfile  = $this->storage->read('token_child');

        // Check child has link to parent
        $this->assertNotNull($childProfile->getParent());
        $this->assertEquals($parentProfile->getToken(), $childProfile->getParentToken());

        // Check parent has child
        $children = $parentProfile->getChildren();
        $this->assertCount(1, $children);
        $this->assertEquals($childProfile->getToken(), $children[0]->getToken());
    }

    public function testStoreSpecialCharsInUrl()
    {
        // The storage accepts special characters in URLs (Even though URLs are not
        // supposed to contain them)
        $profile = new Ecocode_Profiler_Model_Profile('simple_quote');
        $profile->setUrl('http://foo.bar/\'');
        $this->storage->write($profile);
        $this->assertTrue(false !== $this->storage->read('simple_quote'), '->write() accepts single quotes in URL');

        $profile = new Ecocode_Profiler_Model_Profile('double_quote');
        $profile->setUrl('http://foo.bar/"');
        $this->storage->write($profile);
        $this->assertTrue(false !== $this->storage->read('double_quote'), '->write() accepts double quotes in URL');

        $profile = new Ecocode_Profiler_Model_Profile('backslash');
        $profile->setUrl('http://foo.bar/\\');
        $this->storage->write($profile);
        $this->assertTrue(false !== $this->storage->read('backslash'), '->write() accepts backslash in URL');

        $profile = new Ecocode_Profiler_Model_Profile('comma');
        $profile->setUrl('http://foo.bar/,');
        $this->storage->write($profile);
        $this->assertTrue(false !== $this->storage->read('comma'), '->write() accepts comma in URL');
    }

    public function testStoreDuplicateToken()
    {
        $profile = new Ecocode_Profiler_Model_Profile('token');
        $profile->setUrl('http://example.com/');

        $this->assertTrue($this->storage->write($profile), '->write() returns true when the token is unique');

        $profile->setUrl('http://example.net/');

        $this->assertTrue($this->storage->write($profile), '->write() returns true when the token is already present in the storage');
        $this->assertEquals('http://example.net/', $this->storage->read('token')->getUrl(), '->write() overwrites the current profile data');

        $this->assertCount(1, $this->storage->find('', '', 1000, ''), '->find() does not return the same profile twice');
    }

    public function testRetrieveByIp()
    {
        $profile = new Ecocode_Profiler_Model_Profile('token');
        $profile->setIp('127.0.0.1');
        $profile->setMethod('GET');
        $this->storage->write($profile);

        $this->assertCount(1, $this->storage->find('127.0.0.1', '', 10, 'GET'), '->find() retrieve a record by IP');
        $this->assertCount(0, $this->storage->find('127.0.%.1', '', 10, 'GET'), '->find() does not interpret a "%" as a wildcard in the IP');
        $this->assertCount(0, $this->storage->find('127.0._.1', '', 10, 'GET'), '->find() does not interpret a "_" as a wildcard in the IP');
    }

    public function testRetrieveByStatusCode()
    {
        $profile200 = new Ecocode_Profiler_Model_Profile('statuscode200');
        $profile200->setStatusCode(200);
        $this->storage->write($profile200);

        $profile404 = new Ecocode_Profiler_Model_Profile('statuscode404');
        $profile404->setStatusCode(404);
        $this->storage->write($profile404);

        $this->assertCount(1, $this->storage->find(null, null, 10, null, null, null, '200'), '->find() retrieve a record by Status code 200');
        $this->assertCount(1, $this->storage->find(null, null, 10, null, null, null, '404'), '->find() retrieve a record by Status code 404');
    }

    public function testRetrieveByUrl()
    {
        $profile = new Ecocode_Profiler_Model_Profile('simple_quote');
        $profile->setIp('127.0.0.1');
        $profile->setUrl('http://foo.bar/\'');
        $profile->setMethod('GET');
        $this->storage->write($profile);

        $profile = new Ecocode_Profiler_Model_Profile('double_quote');
        $profile->setIp('127.0.0.1');
        $profile->setUrl('http://foo.bar/"');
        $profile->setMethod('GET');
        $this->storage->write($profile);

        $profile = new Ecocode_Profiler_Model_Profile('backslash');
        $profile->setIp('127.0.0.1');
        $profile->setUrl('http://foo\\bar/');
        $profile->setMethod('GET');
        $this->storage->write($profile);

        $profile = new Ecocode_Profiler_Model_Profile('percent');
        $profile->setIp('127.0.0.1');
        $profile->setUrl('http://foo.bar/%');
        $profile->setMethod('GET');
        $this->storage->write($profile);

        $profile = new Ecocode_Profiler_Model_Profile('underscore');
        $profile->setIp('127.0.0.1');
        $profile->setUrl('http://foo.bar/_');
        $profile->setMethod('GET');
        $this->storage->write($profile);

        $profile = new Ecocode_Profiler_Model_Profile('semicolon');
        $profile->setIp('127.0.0.1');
        $profile->setUrl('http://foo.bar/;');
        $profile->setMethod('GET');
        $this->storage->write($profile);

        $this->assertCount(1, $this->storage->find('127.0.0.1', 'http://foo.bar/\'', 10, 'GET'), '->find() accepts single quotes in URLs');
        $this->assertCount(1, $this->storage->find('127.0.0.1', 'http://foo.bar/"', 10, 'GET'), '->find() accepts double quotes in URLs');
        $this->assertCount(1, $this->storage->find('127.0.0.1', 'http://foo\\bar/', 10, 'GET'), '->find() accepts backslash in URLs');
        $this->assertCount(1, $this->storage->find('127.0.0.1', 'http://foo.bar/;', 10, 'GET'), '->find() accepts semicolon in URLs');
        $this->assertCount(1, $this->storage->find('127.0.0.1', 'http://foo.bar/%', 10, 'GET'), '->find() does not interpret a "%" as a wildcard in the URL');
        $this->assertCount(1, $this->storage->find('127.0.0.1', 'http://foo.bar/_', 10, 'GET'), '->find() does not interpret a "_" as a wildcard in the URL');
    }

    public function testStoreTime()
    {
        $dt    = new \DateTime('now');
        $start = $dt->getTimestamp();

        for ($i = 0; $i < 3; ++$i) {
            $dt->modify('+1 minute');
            $profile = new Ecocode_Profiler_Model_Profile('time_' . $i);
            $profile->setIp('127.0.0.1');
            $profile->setUrl('http://foo.bar');
            $profile->setTime($dt->getTimestamp());
            $profile->setMethod('GET');
            $this->storage->write($profile);
        }

        $records = $this->storage->find('', '', 3, 'GET', $start, time() + 3 * 60);
        $this->assertCount(3, $records, '->find() returns all previously added records');
        $this->assertEquals($records[0]['token'], 'time_2', '->find() returns records ordered by time in descendant order');
        $this->assertEquals($records[1]['token'], 'time_1', '->find() returns records ordered by time in descendant order');
        $this->assertEquals($records[2]['token'], 'time_0', '->find() returns records ordered by time in descendant order');

        $records = $this->storage->find('', '', 3, 'GET', $start, time() + 2 * 60);
        $this->assertCount(2, $records, '->find() should return only first two of the previously added records');
    }

    public function testRetrieveByEmptyUrlAndIp()
    {
        for ($i = 0; $i < 5; ++$i) {
            $profile = new Ecocode_Profiler_Model_Profile('token_' . $i);
            $profile->setMethod('GET');
            $this->storage->write($profile);
        }
        $this->assertCount(5, $this->storage->find('', '', 10, 'GET'), '->find() returns all previously added records');
        $this->storage->purge();
    }

    public function testRetrieveByMethodAndLimit()
    {
        foreach (['POST', 'GET'] as $method) {
            for ($i = 0; $i < 5; ++$i) {
                $profile = new Ecocode_Profiler_Model_Profile('token_' . $i . $method);
                $profile->setMethod($method);
                $this->storage->write($profile);
            }
        }

        $this->assertCount(5, $this->storage->find('', '', 5, 'POST'));

        $this->storage->purge();
    }

    public function testPurge()
    {
        $profile = new Ecocode_Profiler_Model_Profile('token1');
        $profile->setIp('127.0.0.1');
        $profile->setUrl('http://example.com/');
        $profile->setMethod('GET');
        $this->storage->write($profile);

        $this->assertTrue(false !== $this->storage->read('token1'));
        $this->assertCount(1, $this->storage->find('127.0.0.1', '', 10, 'GET'));

        $profile = new Ecocode_Profiler_Model_Profile('token2');
        $profile->setIp('127.0.0.1');
        $profile->setUrl('http://example.net/');
        $profile->setMethod('GET');
        $this->storage->write($profile);

        $this->assertTrue(false !== $this->storage->read('token2'));
        $this->assertCount(2, $this->storage->find('127.0.0.1', '', 10, 'GET'));

        $this->storage->purge();

        $this->assertEmpty($this->storage->read('token'), '->purge() removes all data stored by profiler');
        $this->assertCount(0, $this->storage->find('127.0.0.1', '', 10, 'GET'), '->purge() removes all items from index');
    }

    public function testDuplicates()
    {
        for ($i = 1; $i <= 5; ++$i) {
            $profile = new Ecocode_Profiler_Model_Profile('foo' . $i);
            $profile->setIp('127.0.0.1');
            $profile->setUrl('http://example.net/');
            $profile->setMethod('GET');

            ///three duplicates
            $this->storage->write($profile);
            $this->storage->write($profile);
            $this->storage->write($profile);
        }
        $this->assertCount(3, $this->storage->find('127.0.0.1', 'http://example.net/', 3, 'GET'), '->find() method returns incorrect number of entries');
    }

    public function testStatusCode()
    {
        $profile = new Ecocode_Profiler_Model_Profile('token1');
        $profile->setStatusCode(200);
        $this->storage->write($profile);

        $profile = new Ecocode_Profiler_Model_Profile('token2');
        $profile->setStatusCode(404);
        $this->storage->write($profile);

        $tokens = $this->storage->find('', '', 10, '');
        $this->assertCount(2, $tokens);
        $this->assertContains($tokens[0]['status_code'], [200, 404]);
        $this->assertContains($tokens[1]['status_code'], [200, 404]);
    }

    public function testMultiRowIndexFile()
    {
        $iteration = 3;
        for ($i = 0; $i < $iteration; ++$i) {
            $profile = new Ecocode_Profiler_Model_Profile('token' . $i);
            $profile->setIp('127.0.0.' . $i);
            $profile->setUrl('http://foo.bar/' . $i);

            $this->storage->write($profile);
            $this->storage->write($profile);
            $this->storage->write($profile);
        }

        $handle = fopen($this->tmpDir . '/index.csv', 'r');
        for ($i = 0; $i < $iteration; ++$i) {
            $row = fgetcsv($handle);
            $this->assertEquals('token' . $i, $row[0]);
            $this->assertEquals('127.0.0.' . $i, $row[1]);
            $this->assertEquals('http://foo.bar/' . $i, $row[3]);
        }
        $this->assertFalse(fgetcsv($handle));
    }

    public function testReadLineFromFile()
    {
        $r = new \ReflectionMethod($this->storage, 'readLineFromFile');

        $r->setAccessible(true);

        $h = tmpfile();

        fwrite($h, "line1\n\n\nline2\n");
        fseek($h, 0, SEEK_END);

        $this->assertEquals('line2', $r->invoke($this->storage, $h));
        $this->assertEquals('line1', $r->invoke($this->storage, $h));
    }

    protected function cleanDir()
    {
        $flags    = \FilesystemIterator::SKIP_DOTS;
        $iterator = new \RecursiveDirectoryIterator($this->tmpDir, $flags);
        $iterator = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
