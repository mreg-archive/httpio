<?php
namespace itbz\httpio;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testGetStatusDesc()
    {
        $this->assertEquals('Continue', Response::getStatusDesc(100));
    }

    public function testToCamelCase()
    {
        $this->assertEquals('Foo', Response::toCamelCase('foo'));
        $this->assertEquals('Content-Type', Response::toCamelCase('content-type'));
        $this->assertEquals('Content-Type', Response::toCamelCase('CONTENT-type'));
    }

    public function testSetGetStatus()
    {
        $response = new Response();
        $this->assertSame(200, $response->getStatus());

        $response->setStatus(404);
        $this->assertSame(404, $response->getStatus());
    }

    public function testGetStatusHeader()
    {
        $response = new Response();

        if (strpos(PHP_SAPI, 'fcgi') !== false) {
            $this->assertEquals('Status: 200 OK', $response->getStatusHeader());
        } else {
            $this->assertEquals('HTTP/1.1 200 OK', $response->getStatusHeader());
        }
    }

    public function testContent()
    {
        $response = new Response();
        $this->assertSame('', $response->getContent());

        $response->addContent('yo');
        $this->assertSame('yo', $response->getContent());

        $response->setContent('foobar');
        $this->assertSame('foobar', $response->getContent());

        $response->clearContent('foobar');
        $this->assertSame('', $response->getContent());
    }

    public function testSetGetHeader()
    {
        $response = new Response();
        $this->assertSame('', $response->getHeader('Foo'));

        $response->setHeader('Foo', 'bar');
        $this->assertSame('bar', $response->getHeader('Foo'));

        // Case-insensitive get
        $this->assertSame('bar', $response->getHeader('FOO'));

        // Set should overwrite
        $response->setHeader('Foo', 'foobar');
        $this->assertSame('foobar', $response->getHeader('Foo'));
    }

    public function testAddHeader()
    {
        $response = new Response();
        $response->addHeader('Foo', 'foo');
        $response->addHeader('Foo', 'bar');
        $this->assertSame('foo, bar', $response->getHeader('Foo'));
    }

    public function testIsHeader()
    {
        $response = new Response();
        $this->assertFalse($response->isHeader('Foo'));

        $response->setHeader('Foo', 'bar');
        $this->assertTrue($response->isHeader('Foo'));

        // Case-insesitive search
        $this->assertTrue($response->isHeader('FOO'));
    }

    public function testRemoveHeader()
    {
        $response = new Response();

        $response->setHeader('Foo', 'bar');
        $this->assertTrue($response->isHeader('Foo'));

        $response->removeHeader('Foo');
        $this->assertFalse($response->isHeader('Foo'));

        $response->setHeader('Foo', 'bar');
        $this->assertTrue($response->isHeader('Foo'));

        // Case-insensitive remove
        $response->removeHeader('FOO');
        $this->assertFalse($response->isHeader('Foo'));
    }

    public function testGetHeaders()
    {
        $response = new Response();
        $response->setHeader('Foo', 'bar');
        $response->addHeader('Bar', 'foo');
        $response->addHeader('Bar', 'bar');
        $expected = array(
            'Foo: bar',
            'Bar: foo',
            'Bar: bar',
        );
        $this->assertEquals($expected, $response->getHeaders());
    }

    public function testAddWarning()
    {
        $response = new Response();
        $response->addWarning('foo');
        $response->addWarning('bar');

        $expected = array(
            'Warning: 199 foo',
            'Warning: 199 bar'
        );
        $this->assertEquals($expected, $response->getHeaders());
    }

    public function testAddPersistentWarning()
    {
        $response = new Response();
        $response->addPersistentWarning('foo');
        $response->addPersistentWarning('bar');

        $expected = array(
            'Warning: 299 foo',
            'Warning: 299 bar'
        );
        $this->assertEquals($expected, $response->getHeaders());
    }

    public function testSetFile()
    {
        $response = new Response();
        $response->setFile('contents', 'download.txt');

        $this->assertSame('contents', $response->getContent());

        $expected = array(
            'Content-Type: application/x-download',
            'Content-Disposition: attachment; filename=download.txt'
        );
        $this->assertEquals($expected, $response->getHeaders());
    }

    public function testConstructor()
    {
        $response = new Response('content', '201', array('Foo' => 'bar'));
        $this->assertSame('content', $response->getContent());
        $this->assertSame(201, $response->getStatus());

        $expected = array(
            'Foo: bar'
        );
        $this->assertEquals($expected, $response->getHeaders());
    }

    public function testSend()
    {
        ob_start();
        $response = new Response('content', 200, array('Foo' => 'bar'));
        $response->send();
        $this->assertEquals('content', ob_get_contents());
        ob_end_clean();
    }
}
