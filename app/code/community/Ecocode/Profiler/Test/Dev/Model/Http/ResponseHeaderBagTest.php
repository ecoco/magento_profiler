<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * @group time-sensitive
 */
class ResponseHeaderBagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideAllPreserveCase
     */
    public function testAllPreserveCase($headers, $expected)
    {
        $bag = new Ecocode_Profiler_Model_Http_ResponseHeaderBag($headers);

        $this->assertEquals($expected, $bag->allPreserveCase(), '->allPreserveCase() gets all input keys in original case');
    }

    public function provideAllPreserveCase()
    {
        return array(
            array(
                array('fOo' => 'BAR'),
                array('fOo' => array('BAR'), 'Cache-Control' => array('no-cache')),
            ),
            array(
                array('ETag' => 'xyzzy'),
                array('ETag' => array('xyzzy'), 'Cache-Control' => array('private, must-revalidate')),
            ),
            array(
                array('Content-MD5' => 'Q2hlY2sgSW50ZWdyaXR5IQ=='),
                array('Content-MD5' => array('Q2hlY2sgSW50ZWdyaXR5IQ=='), 'Cache-Control' => array('no-cache')),
            ),
            array(
                array('P3P' => 'CP="CAO PSA OUR"'),
                array('P3P' => array('CP="CAO PSA OUR"'), 'Cache-Control' => array('no-cache')),
            ),
            array(
                array('WWW-Authenticate' => 'Basic realm="WallyWorld"'),
                array('WWW-Authenticate' => array('Basic realm="WallyWorld"'), 'Cache-Control' => array('no-cache')),
            ),
            array(
                array('X-UA-Compatible' => 'IE=edge,chrome=1'),
                array('X-UA-Compatible' => array('IE=edge,chrome=1'), 'Cache-Control' => array('no-cache')),
            ),
            array(
                array('X-XSS-Protection' => '1; mode=block'),
                array('X-XSS-Protection' => array('1; mode=block'), 'Cache-Control' => array('no-cache')),
            ),
        );
    }

    public function testReplace()
    {
        $bag = new Ecocode_Profiler_Model_Http_ResponseHeaderBag(array());
        $this->assertEquals('no-cache', $bag->get('Cache-Control'));

        $bag->replace(array('Cache-Control' => 'public'));
        $this->assertEquals('public', $bag->get('Cache-Control'));
    }

    public function testReplaceWithRemove()
    {
        $bag = new Ecocode_Profiler_Model_Http_ResponseHeaderBag(array());
        $this->assertEquals('no-cache', $bag->get('Cache-Control'));

        $bag->remove('Cache-Control');
        $bag->replace(array());
        $this->assertEquals('no-cache', $bag->get('Cache-Control'));
    }


    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetCookiesWithInvalidArgument()
    {
        $bag = new Ecocode_Profiler_Model_Http_ResponseHeaderBag();

        $cookies = $bag->getCookies('invalid_argument');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMakeDispositionInvalidDisposition()
    {
        $headers = new Ecocode_Profiler_Model_Http_ResponseHeaderBag();

        $headers->makeDisposition('invalid', 'foo.html');
    }

    /**
     * @dataProvider provideMakeDisposition
     */
    public function testMakeDisposition($disposition, $filename, $filenameFallback, $expected)
    {
        $headers = new Ecocode_Profiler_Model_Http_ResponseHeaderBag();

        $this->assertEquals($expected, $headers->makeDisposition($disposition, $filename, $filenameFallback));
    }

    public function testToStringDoesntMessUpHeaders()
    {
        $headers = new Ecocode_Profiler_Model_Http_ResponseHeaderBag();

        $headers->set('Location', 'http://www.symfony.com');
        $headers->set('Content-type', 'text/html');

        (string) $headers;

        $allHeaders = $headers->allPreserveCase();
        $this->assertEquals(array('http://www.symfony.com'), $allHeaders['Location']);
        $this->assertEquals(array('text/html'), $allHeaders['Content-type']);
    }

    public function provideMakeDisposition()
    {
        return array(
            array('attachment', 'foo.html', 'foo.html', 'attachment; filename="foo.html"'),
            array('attachment', 'foo.html', '', 'attachment; filename="foo.html"'),
            array('attachment', 'foo bar.html', '', 'attachment; filename="foo bar.html"'),
            array('attachment', 'foo "bar".html', '', 'attachment; filename="foo \\"bar\\".html"'),
            array('attachment', 'foo%20bar.html', 'foo bar.html', 'attachment; filename="foo bar.html"; filename*=utf-8\'\'foo%2520bar.html'),
            array('attachment', 'föö.html', 'foo.html', 'attachment; filename="foo.html"; filename*=utf-8\'\'f%C3%B6%C3%B6.html'),
        );
    }

    /**
     * @dataProvider provideMakeDispositionFail
     * @expectedException \InvalidArgumentException
     */
    public function testMakeDispositionFail($disposition, $filename)
    {
        $headers = new Ecocode_Profiler_Model_Http_ResponseHeaderBag();

        $headers->makeDisposition($disposition, $filename);
    }

    public function provideMakeDispositionFail()
    {
        return array(
            array('attachment', 'foo%20bar.html'),
            array('attachment', 'foo/bar.html'),
            array('attachment', '/foo.html'),
            array('attachment', 'foo\bar.html'),
            array('attachment', '\foo.html'),
            array('attachment', 'föö.html'),
        );
    }
}
