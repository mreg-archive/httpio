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


/**
 * Mock version of Upload for testing
 * @package httpio
 */
class UploadMock extends Upload
{

    /**
     * Custom flag. Set to true if moveUploadedFile should succeed
     * @var bool $forceMockLogic
     */
    public $forceMockLogic = FALSE;


    /**
     * All files are uploaded files in Mock
     * @param string $fname
     * @return bool
     */
    protected function isUploadedFile($fname)
    {
        return TRUE;
    }


    /**
     * If forceMockLogic is set copy() is used instead of move_uploaded_file()
     * @param string $fname
     * @param string $destination
     * @return bool
     */
    protected function moveUploadedFile($fname, $destination)
    {
        if ( $this->forceMockLogic ) {
            return copy($fname, $destination);
        } else {
            return parent::moveUploadedFile($fname, $destination);
        }
    }

}
