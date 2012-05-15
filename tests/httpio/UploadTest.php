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


// Include mock class
include_once "UploadMock.php";


/**
 * Test the Upload class
 * @package httpio
 * @subpackage Tests
 */
class UploadTest extends PHPUnit_Framework_TestCase
{

    /**
     * Create temporary dir and file for testing
     */
    protected function setUp()
    {
        $this->temporaryFileName = tempnam(sys_get_temp_dir(), 'UploadTest');
        $this->temporaryDir = tempnam(sys_get_temp_dir(), 'UploadTest');
        unlink($this->temporaryDir);
        mkdir($this->temporaryDir);
    }


    /**
     * Remove temporary dir and file
     */
    protected function tearDown()
    {
        unlink($this->temporaryFileName);
        rmdir($this->temporaryDir);
    }


    /**
     * Error code triggers exception
     * @expectedException itbz\httpio\Exceptions\FileUploadException
     */
    function testUploadError()
    {
        $u = new Upload('target', 'temp', 0, 'text/plan', UPLOAD_ERR_INI_SIZE);
    }


    /**
     * Temporary file does not exist
     * @expectedException itbz\httpio\Exceptions\FileUploadException
     */
    function testFileReadableError()
    {
        $u = new Upload('target', 'not-readable-temp-name', 0, 'text/plan', UPLOAD_ERR_OK);
    }


    /**
     * Temporary file exists, but is not of size 123
     * @expectedException itbz\httpio\Exceptions\FileUploadException
     */
    function testFileSizeError()
    {
        $u = new Upload('target', $this->temporaryFileName, 123, 'text/plan', UPLOAD_ERR_OK);
    }    


    /**
     * Unvalid type triggers exception
     * @expectedException itbz\httpio\Exceptions\FileUploadException
     */
    function testTypeError()
    {
        $u = new Upload('target', $this->temporaryFileName, 0, 'text', UPLOAD_ERR_OK);
    }
    

    /**
     * Temporary file exists, but is not uploaded
     * @expectedException itbz\httpio\Exceptions\FileUploadException
     */
    function testIsUploadedFileError()
    {
        $u = new Upload('target', $this->temporaryFileName, 0, 'text/plan', UPLOAD_ERR_OK);
    }


    function testSetGetTargetName()
    {
        $u = new UploadMock('target', $this->temporaryFileName, 0, 'text/plan', UPLOAD_ERR_OK);
        $this->assertEquals('target', $u->getTargetName());

        $u->setTargetName('foo');
        $this->assertEquals('foo', $u->getTargetName());
    }


    function testSanitizeName()
    {
        $u = new UploadMock('target<b>', $this->temporaryFileName, 0, 'text/plan', UPLOAD_ERR_OK);
        $this->assertEquals('target', $u->getTargetName());
    }


    function testGetTempNameAndType()
    {
        $u = new UploadMock('target', $this->temporaryFileName, 0, 'text/plan', UPLOAD_ERR_OK);
        $this->assertEquals($this->temporaryFileName, $u->getTempName());
        $this->assertEquals('text/plan', $u->getType());
    }


    /**
     * Target dir does not exist
     * @expectedException itbz\httpio\Exceptions\FileUploadException
     */
    function testMoveToDirError()
    {
        $u = new UploadMock('target', $this->temporaryFileName, 0, 'text/plan', UPLOAD_ERR_OK);
        $u->moveToDir('unexisting-dir-somewhere');
    }


    /**
     * Target dir exists but file is not an uploaded file
     * @expectedException itbz\httpio\Exceptions\FileUploadException
     */
    function testMoveToDirNotUploaded()
    {
        $u = new UploadMock('target', $this->temporaryFileName, 0, 'text/plan', UPLOAD_ERR_OK);
        $u->moveToDir($this->temporaryDir);
    }


    function testMoveToDir()
    {
        $u = new UploadMock('target', $this->temporaryFileName, 0, 'text/plan', UPLOAD_ERR_OK);
        $u->forceMockLogic = TRUE;

        $u->moveToDir($this->temporaryDir);

        $fname = $this->temporaryDir . DIRECTORY_SEPARATOR . 'target';
        $this->assertTrue(is_file($fname));
        unlink($fname);
    }


    function testGetContents()
    {
        $u = new UploadMock('target', $this->temporaryFileName, 0, 'text/plan', UPLOAD_ERR_OK);
        $data = $u->getContents();
        $this->assertEquals('', $data);
    }

}
