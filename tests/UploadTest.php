<?php
namespace itbz\httpio;


class UploadTest extends \PHPUnit_Framework_TestCase
{

    function setUp()
    {
        // Create temporary dir and file for testing
        $this->temporaryFileName = tempnam(sys_get_temp_dir(), 'UploadTest');
        $this->temporaryDir = tempnam(sys_get_temp_dir(), 'UploadTest');
        unlink($this->temporaryDir);
        mkdir($this->temporaryDir);
    }


    function tearDown()
    {
        // Remove temporary dir and file
        unlink($this->temporaryFileName);
        rmdir($this->temporaryDir);
    }


    /**
     * Error code triggers exception
     * @expectedException itbz\httpio\Exception\FileUploadException
     */
    function testUploadError()
    {
        $u = new Upload('target', 'temp', 0, 'text/plan', UPLOAD_ERR_INI_SIZE);
    }


    /**
     * Temporary file does not exist
     * @expectedException itbz\httpio\Exception\FileUploadException
     */
    function testFileReadableError()
    {
        $u = new Upload('target', 'not-readable-temp-name', 0, 'text/plan', UPLOAD_ERR_OK);
    }


    /**
     * Temporary file exists, but is not of size 123
     * @expectedException itbz\httpio\Exception\FileUploadException
     */
    function testFileSizeError()
    {
        $u = new Upload('target', $this->temporaryFileName, 123, 'text/plan', UPLOAD_ERR_OK);
    }    


    /**
     * Unvalid type triggers exception
     * @expectedException itbz\httpio\Exception\FileUploadException
     */
    function testTypeError()
    {
        $u = new Upload('target', $this->temporaryFileName, 0, 'text', UPLOAD_ERR_OK);
    }
    

    /**
     * Temporary file exists, but is not uploaded
     * @expectedException itbz\httpio\Exception\FileUploadException
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
     * @expectedException itbz\httpio\Exception\FileUploadException
     */
    function testMoveToDirError()
    {
        $u = new UploadMock('target', $this->temporaryFileName, 0, 'text/plan', UPLOAD_ERR_OK);
        $u->moveToDir('unexisting-dir-somewhere');
    }


    /**
     * Target dir exists but file is not an uploaded file
     * @expectedException itbz\httpio\Exception\FileUploadException
     */
    function testMoveToDirNotUploaded()
    {
        $u = new UploadMock('target', $this->temporaryFileName, 0, 'text/plan', UPLOAD_ERR_OK);
        $u->moveToDir($this->temporaryDir);
    }


    function testMoveToDir()
    {
        $u = new UploadCopyFile('target', $this->temporaryFileName, 0, 'text/plan', UPLOAD_ERR_OK);
        //$u->forceMockLogic = TRUE;

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

class UploadMock extends Upload
{
    protected function isUploadedFile($fname)
    {
        return TRUE;
    }
}

class UploadCopyFile extends UploadMock
{
    protected function moveUploadedFile($fname, $destination)
    {
        return copy($fname, $destination);
    }
}
