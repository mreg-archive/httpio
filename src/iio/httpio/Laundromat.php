<?php
/**
 * This file is part of the httpio package
 *
 * Copyright (c) 2012 Hannes Forsgård
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace iio\httpio;

use iio\httpio\Exception\DataNotSetException;
use iio\httpio\Exception\DataNotValidException;

/**
 * Class for storing and validating unclean data.
 * 
 * Laundromat stores data in a case insensitive manner. Data keys
 * differing only in case will be treated as identical and the former key-data
 * pair will be overwritten.
 *
 * @author  Hannes Forsgård <hannes.forsgard@gmail.com>
 * @package httpio
 */
class Laundromat
{
    /**
     * Internal data store
     *
     * @var array
     */
    private $data;

    /**
     * Set associative array with data.
     *
     * Laundromat stores data in a case insensitive manner. Data keys
     * differing only in case will be treated as identical and the former
     * key-data pair will be overwritten.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = array_change_key_case($data);
    }

    /**
     * Remove data associated with $key
     *
     * @param string $key
     *
     * @return void
     */
    public function remove($key)
    {
        assert('is_string($key)');
        $key = strtolower($key);
        unset($this->data[$key]);
    }

    /**
     * Check if $key is set
     *
     * @param string $key
     *
     * @return bool
     */
    public function is($key)
    {
        assert('is_string($key)');
        $key = strtolower($key);

        return isset($this->data[$key]);
    }

    /**
     * Get filtered value from $key.
     *
     * Filter may be a callback function. In this case value is returned if
     * callback returns true. Filter may be a filter_var validation filter. And
     * lastly filter may be a regular expression. In this case value is returned
     * if it matches the expression. NOTE: delimiter must always be '/'
     *
     * @param string $key
     * @param mixed $filter
     *
     * @return mixed The data associated with $key
     *
     * @throws DataNotSetException if data is not set
     * @throws DataNotValidException if unvalid data is held back
     */
    public function get($key, $filter)
    {
        assert('is_string($key)');
        $key = strtolower($key);

        if (!$this->is($key)) {
            $msg = "Request data for key '$key' missing.";
            throw new DataNotSetException($msg, 400);
        }

        $value = $this->data[$key];

        if (is_callable($filter)) {
            // Use callback function
            if ($filter($value)) {

                return $value;
            }

        } elseif (is_long($filter)) {
            // Use sanitising filter

            return filter_var($value, $filter);

        } elseif (is_string($filter) && is_scalar($value)) {
            // Use regular expression
            if (preg_match($filter, $value)) {

                return $value;
            }
        }

        // Still here, data was not valid
        $msg = "Request data for key '$key' not valid.";
        throw new DataNotValidException($msg, 400);
    }
}
