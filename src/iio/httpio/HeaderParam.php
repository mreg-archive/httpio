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

namespace iio\httpio;


/**
 * Helper class to read ; separated params from headers
 *
 * @package httpio
 */
class HeaderParam
{
    /**
     * List of params
     *
     * @var array
     */
    private $params = array();

    /**
     * Base header param
     *
     * @var string
     */
    private $base;

    /**
     * Set values from header
     *
     * @param string $header
     */
    public function __construct($header)
    {
        assert('is_string($header)');
        $parts = explode(';', $header);
        foreach ($parts as $part) {
            $subparts = explode('=', $part, 2);

            // Trim all subparts
            $subparts = array_map(
                function ($value) {
                    return trim($value, " \t\n\r\0\x0B'\"");
                },
                $subparts
            );

            if (count($subparts) > 1) {
                // Save parameter
                list($key, $value) = $subparts;
                $this->params[$key] = $value;
            } else {
                // Save base param
                $this->base = $subparts[0];
            }
        }
    }

    /**
     * Get header base param
     *
     * @return string
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * Get parameter from name
     *
     * @param string $name
     *
     * @return string
     */
    public function getParam($name)
    {
        return isset($this->params[$name]) ? $this->params[$name] : '';
    }
}
