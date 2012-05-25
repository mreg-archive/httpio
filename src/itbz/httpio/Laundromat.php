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
use itbz\httpio\Exception\DataNotSetException;
use itbz\httpio\Exception\DataNotValidException;


/**
 * Class for storing and validating unclean data.
 * 
 * Laundromat stores data in a case insensitive manner. Data keys
 * differing only in case will be treated as identical and the former key-data
 * pair will be overwritten.
 *
 * @package httpio
 */
class Laundromat
{

    /**
     * Internal data store
     *
     * @var array
     */
    private $_data;


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
        $this->_data = array_change_key_case($data);
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
        unset($this->_data[$key]);
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
        return isset($this->_data[$key]);
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
     *
     * @param mixed $filter
     *
     * @return mixed The data associated with $key
     *
     * @throws DataNotSetException if data is not set
     *
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

        $value = $this->_data[$key];

        // Use callback function
        if (is_callable($filter)) {
            if ($filter($value)) {
                return $value;
            }

        // Use sanitising filter
        } elseif (is_long($filter)) {
            return filter_var($value, $filter);

        // Use regular expression
        } elseif (is_string($filter) && is_scalar($value)) {
            if (preg_match($filter, $value)) {
                return $value;
            }
        }

        // Still here, data was not valid
        $msg = "Request data for key '$key' not valid.";
        throw new DataNotValidException($msg, 400);
    }

}
