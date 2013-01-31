<?php
namespace iio\httpio;

class UploadTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // Create temporary dir and file for testing
        $this->temporaryFileName = tempnam(sys_get_temp_dir(), 'UploadTest');
        $this->temporaryDir = tempnam(sys_get_temp_dir(), 'UploadTest');
        unlink($this->temporaryDir);
        mkdir($this->temporaryDir);
    }

    public function tearDown()
    {
        // Remove temporary dir and file
        unlink($this->temporaryFileName);
        rmdir($this->temporaryDir);
    }

    /**
     * Error code triggers exception
     * 
     * @expectedException iio\httpio\Exception\FileUploadException
     */
    public function testUploadError()
    {
        new Upload('target', 'temp', 0, 'text/plan', UPLOAD_ERR_INI_SIZE);
    }

    /**
     * Temporary file does not exist
     * 
     * @expectedException iio\httpio\Exception\FileUploadException
     */
    public function testFileReadableError()
    {
        new Upload('target', 'not-readable-temp-name', 0, 'text/plan', UPLOAD_ERR_OK);
    }

    /**
     * Temporary file exists, but is not of size 123
     * 
     * @expectedException iio\httpio\Exception\FileUploadException
     */
    public function testFileSizeError()
    {
        new Upload('target', $this->temporaryFileName, 123, 'text/plan', UPLOAD_ERR_OK);
    }

    /**
     * Unvalid type triggers exception
     * 
     * @expectedException iio\httpio\Exception\FileUploadException
     */
    public function testTypeError()
    {
        new Upload('target', $this->temporaryFileName, 0, 'text', UPLOAD_ERR_OK);
    }

    /**
     * Temporary file exists, but is not uploaded
     * 
     * @expectedException iio\httpio\Exception\FileUploadException
     */
    public function testIsUploadedFileError()
    {
         new Upload('target', $this->temporaryFileName, 0, 'text/plan', UPLOAD_ERR_OK);
    }

    public function testSetGetTargetName()
    {
        $u = new UploadMock('target', $this->temporaryFileName, 0, 'text/plan', UPLOAD_ERR_OK);
        $this->assertEquals('target', $u->getTargetName());

        $u->setTargetName('foo');
        $this->assertEquals('foo', $u->getTargetName());
    }

    public function testSanitizeName()
    {
        $u = new UploadMock('target<b>', $this->temporaryFileName, 0, 'text/plan', UPLOAD_ERR_OK);
        $this->assertEquals('target', $u->getTargetName());
    }

    public function testGetTempNameAndType()
    {
        $u = new UploadMock('target', $this->temporaryFileName, 0, 'text/plan', UPLOAD_ERR_OK);
        $this->assertEquals($this->temporaryFileName, $u->getTempName());
        $this->assertEquals('text/plan', $u->getType());
    }

    /**
     * Target dir does not exist
     * 
     * @expectedException iio\httpio\Exception\FileUploadException
     */
    public function testMoveToDirError()
    {
        $u = new UploadMock('target', $this->temporaryFileName, 0, 'text/plan', UPLOAD_ERR_OK);
        $u->moveToDir('unexisting-dir-somewhere');
    }

    /**
     * Target dir exists but file is not an uploaded file
     * 
     * @expectedException iio\httpio\Exception\FileUploadException
     */
    public function testMoveToDirNotUploaded()
    {
        $u = new UploadMock('target', $this->temporaryFileName, 0, 'text/plan', UPLOAD_ERR_OK);
        $u->moveToDir($this->temporaryDir);
    }

    public function testMoveToDir()
    {
        $u = new UploadCopyFile('target', $this->temporaryFileName, 0, 'text/plan', UPLOAD_ERR_OK);

        $u->moveToDir($this->temporaryDir);

        $fname = $this->temporaryDir . DIRECTORY_SEPARATOR . 'target';
        $this->assertTrue(is_file($fname));
        unlink($fname);
    }

    public function testGetContents()
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
        return true;
    }
}

class UploadCopyFile extends UploadMock
{
    protected function moveUploadedFile($fname, $destination)
    {
        return copy($fname, $destination);
    }
}
