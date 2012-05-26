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
 *
 * @package httpio
 */
namespace itbz\httpio;
use itbz\httpio\Exception\FileUploadException;


/**
 * Upload objects are used when working with uploaded files
 *
 * @package httpio
 */
class Upload
{

    /**
     * List of PHP upload error codes and descriptive messages
     *
     * @var array
     */
    static private $_uploadErrMsgs = array(
        UPLOAD_ERR_INI_SIZE =>
            "Uploaded file exceeds the upload_max_filesize php directive.",
        UPLOAD_ERR_FORM_SIZE =>
            "Uploaded file exceeds the MAX_FILE_SIZE HTML form directive.",
        UPLOAD_ERR_PARTIAL =>
            "The uploaded file was only partially uploaded.",
        UPLOAD_ERR_NO_FILE =>
            "No file was uploaded.",
        UPLOAD_ERR_NO_TMP_DIR =>
            "Missing a temporary folder.",
        UPLOAD_ERR_CANT_WRITE =>
            "Failed to write file to disk.",
        UPLOAD_ERR_EXTENSION =>
            "A PHP extension stopped the file upload."
    );


    /**
     * User specified target name of file
     *
     * @var string
     */
    private $_targetName;


    /**
     * Temporary file name
     *
     * @var string
     */
    private $_tempName;


    /**
     * MIME-type of uploaded file
     *
     * @var string
     */
    private $_type;


    /**
     * Set values
     *
     * @param string $targetName
     *
     * @param string $tempName
     *
     * @param int $size
     *
     * @param string $type
     *
     * @param int $error
     *
     * @throws FileUploadException If file is not a valid uploaded file
     */
    public function __construct($targetName, $tempName, $size, $type, $error)
    {
        assert('is_string($targetName)');
        assert('is_string($tempName)');
        assert('is_int($size)');
        assert('is_string($type)');
        assert('is_int($error)');

        // Check that upload was successfull
        if ( $error != UPLOAD_ERR_OK ) {
            throw new FileUploadException(self::$_uploadErrMsgs[$error]);
        }

        // Check that file exists
        if ( !$this->isReadableFile($tempName) ) {
            $msg = "Unable to access temporary file '$tempName' on server";
            throw new FileUploadException($msg);
        }

        // Validate file size
        if ( $this->filesize($tempName) !== $size ) {
            $msg = "Size of '$tempName' does not match expected size '$size'";
            throw new FileUploadException($msg);
        }

        // Validate file type
        if ( !preg_match("/[a-z]+\/[0-7a-z+.-]+/i", $type) ) {
            $msg = "Invalid type '$type' for file '$tempName'";
            throw new FileUploadException($msg);
        }

        // Check that file actually is an uploaded file
        if ( !$this->isUploadedFile($tempName) ) {
            $msg = "Trying to access a not uploaded file '$tempName'";
            throw new FileUploadException($msg);
        }

        // Sanitize name
        $targetName = filter_var($targetName, FILTER_SANITIZE_STRING);

        $this->_targetName = $targetName;
        $this->_tempName = $tempName;
        $this->_type = $type;
    }


    /**
     * Get upload target name
     *
     * @return string
     */
    public function getTargetName()
    {
        return $this->_targetName;
    }


    /**
     * Set new target name
     *
     * @param string $targetName
     *
     * @return void
     */
    public function setTargetName($targetName)
    {
        assert('is_string($targetName)');
        $this->_targetName = $targetName;
    }    


    /**
     * Get upload temporary name
     *
     * @return string
     */
    public function getTempName()
    {
        return $this->_tempName;
    }


    /**
     * Get upload MIME-type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }


    /**
     * Move uploaded file to directory
     *
     * @param string $dirname
     *
     * @return bool TRUE on success, FALSE otherwise
     *
     * @throws FileUploadException if directory is not writable
     */
    public function moveToDir($dirname)
    {
        assert('is_string($dirname)');
        if ( !$this->isWritableDir($dirname) ) {
            $msg = "Unable to write to directory '$dirname'";
            throw new FileUploadException($msg);
        }
        $target = $dirname . DIRECTORY_SEPARATOR . $this->getTargetName();
        
        if ( !$this->moveUploadedFile($this->getTempName(), $target) ) {
            $msg = "Unable to move uploaded file '{$this->getTempName()}'";
            throw new FileUploadException($msg);
        }
    }


    /**
     * Read contents of uploaded file
     *
     * @return string
     *
     * @throws FileUploadException if unable to read contents
     */
    public function getContents()
    {
        $tmpname = $this->getTempName();
        $data = $this->fileGetContents($tmpname);
        
        return $data;
    }


    /**
     * Wrapper to PHP native is_uploaded_file() function
     *
     * @param string $fname
     *
     * @return bool
     */
    protected function isUploadedFile($fname)
    {
        return is_uploaded_file($fname);
    }


    /**
     * Wrapper to PHP native move_uploaded_file() function
     *
     * @param string $fname
     *
     * @param string $destination
     *
     * @return bool
     */
    protected function moveUploadedFile($fname, $destination)
    {
        return @move_uploaded_file($fname, $destination);
    }


    /**
     * Wrapper to PHP natvie is_file() and is_readable() functions
     *
     * @param string $fname
     *
     * @return bool
     */
    protected function isReadableFile($fname)
    {
        return is_file($fname) && is_readable($fname);
    }


    /**
     * Wrapper to PHP native is_dir() and is_writable() functions
     *
     * @param string $dirname
     *
     * @return bool
     */
    protected function isWritableDir($dirname)
    {
        return is_dir($dirname) && is_writable($dirname);
    }


    /**
     * Wrapper to PHP natvie filesize() function
     *
     * @param string $fname
     *
     * @return int
     */
    protected function filesize($fname)
    {
        return filesize($fname);
    }


    /**
     * Wrapper to PHP natvie file_get_contents() function
     *
     * @param string $fname
     *
     * @return string The data read, FALSE on failure
     */
    protected function fileGetContents($fname)
    {
        return file_get_contents($fname);
    }

}
