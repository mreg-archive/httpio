<?php
/**
 * This file is part of the httpio package
 *
 * Copyright (c) 2012 Hannes Forsgård
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Hannes Forsgård <hannes.forsgard@gmail.com>
 * @package httpio
 * @subpackage Tests
 */
namespace itbz\httpio;
use PHPUnit_Framework_TestCase;


// Include mock classes
include_once "HeaderToolMock.php";


/**
 * Test the Response class
 * @package httpio
 * @subpackage Tests
 */
class ResponseTest extends PHPUnit_Framework_TestCase
{

    function testSetGetStatus()
    {
        $r = new Response(new HeaderToolMock());
        $this->assertSame(200, $r->getStatus());
        $r->setStatus(404);
        $this->assertSame(404, $r->getStatus());
    }

    
    function testSetIsRemoveHeader()
    {
        $r = new Response(new HeaderToolMock());
        $this->assertFalse($r->isHeader('Content-Type'));

        $r->setHeader('Content-Type', 'text/html');
        $this->assertTrue($r->isHeader('Content-Type'));

        $r->removeHeader('Content-Type');
        $this->assertFalse($r->isHeader('Content-Type'));
    }


    function testAddGetHeader()
    {
        $r = new Response(new HeaderToolMock());
        $this->assertSame('', $r->getHeader('Foo'));

        $r->addHeader('Foo', 'foo1');
        $this->assertSame('foo1', $r->getHeader('Foo'));

        $r->addHeader('Foo', 'foo2');
        $this->assertSame('foo1, foo2', $r->getHeader('Foo'));
    }


    function testGetallheaders()
    {
        $r = new Response(new HeaderToolMock());
        $r->setHeader('Content-Type', 'text/html');
        $r->setHeader('Content-Language', 'sv');
        $expected = array(
            'Content-Type' => 'text/html',
            'Content-Language' => 'sv'
        );
        $this->assertEquals($expected, $r->getallheaders());
    }


    function testSetWarnings()
    {
        $r = new Response(new HeaderToolMock());
        $r->setWarning('Warning');
        $this->assertEquals('199 Warning', $r->getHeader('Warning'));

        $r->removeHeader('Warning');
        $r->setPersistentWarning('Warning');
        $this->assertEquals('299 Warning', $r->getHeader('Warning'));
    }


    function testSend_file()
    {
        $r = new Response(new HeaderToolMock());
        ob_start();
        $r->send_file('abcdef', 'file.txt');
        $data = ob_get_contents();
        ob_clean();
        $this->assertEquals('abcdef', $data);
        $this->assertEquals('application/x-download', $r->getHeader('Content-Type'));
        $this->assertEquals('attachment; filename=file.txt', $r->getHeader('Content-Disposition'));
    }


    function testGetContentType()
    {
        $r = new Response(new HeaderToolMock());
        $this->assertEquals('', $r->getContentType());

        $r->setHeader('Content-Type', 'text/html; charset=utf8');
        $this->assertEquals('text/html', $r->getContentType());
    }


    function testGetCharset()
    {
        $r = new Response(new HeaderToolMock());
        $this->assertEquals('', $r->getCharset());

        $r->setHeader('Content-Type', 'text/html; charset=utf8');
        $this->assertEquals('utf8', $r->getCharset());
    }


    function testGetLanguage()
    {
        $r = new Response(new HeaderToolMock());
        $this->assertEquals('', $r->getLanguage());

        $r->setHeader('Content-Language', 'sv');
        $this->assertEquals('sv', $r->getLanguage());
    }


    function testGetStatusDesc()
    {
        $this->assertEquals("Internal Server Error", Response::getStatusDesc(500));
        $this->assertEquals("", Response::getStatusDesc(800));
    }

}
