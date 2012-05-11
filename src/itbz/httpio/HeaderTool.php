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
 */
namespace itbz\httpio;


/**
 * Internal class to handle calls to PHP native header functions
 * @package httpio
 * @codeCoverageIgnore
 */
class HeaderTool
{

    /**
     * Set http status
     * @param int $status
     * @param string $desc
     * @return void
     */
    public function status($status, $desc)
    {
        if ( strpos(PHP_SAPI, 'fcgi') !== false ) {
            return header("Status: $status $desc");
        } else {
            return header("HTTP/1.1 $status $desc");
        }
    }


    /**
     * Send a raw HTTP header
     * @param string $name
     * @param string $value
     * @param bool $replace
     * @return void
     */
    public function header($name, $value, $replace = TRUE)
    {
        return header("$name: $value", $replace);
    }


    /**
     * Remove previously set header
     * @param string $name
     * @return void
     */
    public function header_remove($name)
    {
        return header_remove($name);
    }


    /**
     * Returns a numerically indexed array of headers
     * @return array
     */
    public function headers_list()
    {
        return headers_list();
    }

}
