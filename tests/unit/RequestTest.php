<?php
namespace iio\httpio;

use DateTime;
use iio\httpio\Exception\FileUploadException;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    private function getRequest()
    {
        return new Request(
            '192.168.0.1',
            '/index.php',
            'GET',
            array(
                'Accept' => 'application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png',
                'Content-Type' => 'text/html; charset=utf8',
                'If-Match' => '123456789',
                'If-Modified-Since' => 'Sat, 29 Oct 1994 19:43:31 GMT',
            ),
            array(),
            array(),
            array(),
            array(
                'file' => array(
                    'name' => '',
                    'type' => 'text/plain',
                    'size' => 0,
                    'tmp_name' => '',
                    'error' => UPLOAD_ERR_OK,
                ),
            )
        );
    }

    /**
     * @expectedException iio\httpio\Exception
     */
    public function testMethodUnknownError()
    {
        new Request(
            '192.168.0.1',
            '/index.php',
            'FOO',
            array(),
            array(),
            array(),
            array(),
            array()
        );
    }

    public function testGetIpUriMethodContentTypeCharset()
    {
        $r = $this->getRequest();
        $this->assertEquals('192.168.0.1', $r->getIp());
        $this->assertEquals('/index.php', $r->getUri());
        $this->assertEquals('GET', $r->getMethod());
        $this->assertEquals('text/html', $r->getContentType());
        $this->assertEquals('utf8', $r->getCharset());
    }

    public function testMatchEtag()
    {
        $r = $this->getRequest();
        $this->assertTrue($r->matchEtag('123456789'));
        $this->assertFalse($r->matchEtag('kjfhskdfjg'));
    }

    public function testMatchModified()
    {
        $r = $this->getRequest();
        $this->assertFalse($r->matchModified());
        $this->assertTrue($r->matchModified(new DateTime('1980')));
    }

    public function testIsUpload()
    {
        $r = $this->getRequest();
        $this->assertTrue($r->isUpload());
    }

    public function testGetNextUpload()
    {
        $r = $this->getRequest();

        // Test get the one upload registerd in test request
        $exception = false;
        try {
            $r->getNextUpload();
        } catch (FileUploadException $e) {
            // Exception because upload does not actually exist
            $exception = true;
        }
        $this->assertTrue($exception);

        // Test that while loop breaks when there are no more uploads
        $didWhile = false;
        while ($r->getNextUpload()) {
            $didWhile = true;
        }
        $this->assertFalse($didWhile);
    }

    public function testCreateFromGlobals()
    {
        $_SERVER = array(
            'REQUEST_URI' => 'http://localhost/index.php?foo=bar',
            'REMOTE_ADDR' => '192.168.0.1',
            'REQUEST_METHOD' => 'GET',
            'HTTP_USER_AGENT' => 'phpunit'
        );
        $_GET = array();
        $_POST = array();
        $_COOKIE = array();
        $_FILES = array();

        $r = Request::createFromGlobals();

        $this->assertEquals('GET', $r->getMethod());
        $this->assertEquals('192.168.0.1', $r->getIp());
        $this->assertEquals('/index.php', $r->getUri());
        $this->assertEquals('phpunit', $r->headers->get('user-agent', FILTER_SANITIZE_STRING));
    }

    public function testIsMethod()
    {
        $request = new Request();
        $this->assertTrue($request->isMethod('get'));
    }
}
