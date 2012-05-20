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
 * Content negotiation class
 * @package httpio
 *
 * @example
 * // Simple usage
 * $n = new Negotiator(array(
 *      'text/html'=>1.0,
 *      'application/xhtml+xml'=>1.0
 * ));
 * echo $n->negotiate("application/xml,application/xhtml+xml,text/html;q=0.9");
 * // Yields 'application/xml'
 *
 * @example
 * // Negotiate language discarding regional information
 * $n = new Negotiator(array(
 *      'sv'=>1.0,
 *      'en'=>1.0
 * ));
 * $arr = Negotiator::parseRawAccept("en-US,en;q=0.9,sv;q=0.9");
 * $arr = Negotiator::mergeRegion($arr);
 * echo $n->negotiateArray($arr)
 * // Yields 'en'
 */
class Negotiator
{

    /**
     * Array of supported values
     * @var array $_supported
     */
    private $_supported;


    /**
     * Complete result from the last negotiation
     * @var array $_result
     */
    private $_result = array();


    /**
     * Load supported values
     * @param array $supported Associative array with supported values as
     * keys and their respective q-values as values. Supported q-valuse are
     * ignored, but are kept to enable future enhancements.
     */
    public function __construct(array $supported)
    {
        $this->_supported = $supported;
    }


    /**
     * Negotiate from raw accept string
     * @param string $accept
     * @return string Negotiated value 
     */
    public function negotiate($accept)
    {
        assert('is_string($accept)');
        $accept = self::parseRawAccept($accept);
        return $this->negotiateArray($accept);
    }


    /**
     * Get the complete result from the last negotiation
     * @return array
     */
    public function getResult()
    {
        return $this->_result;
    }


    /**
     * Negotiate from array of user acceptable values.
     *
     * The matching value with he highest user accept q-value is returned. Or
     * the default supported value if no values match (the first value in the
     * support array).
     *
     * @param array $accept Associative array with user acceptable values as
     * keys and their respective q-values as values.
     * @return string Negotiated value
     */
    public function negotiateArray(array $accept)
    {
        $this->_result = array();
        
        // Sort based on q-values
        arsort($accept, SORT_NUMERIC);
        
        foreach ($accept as $type => $q) {
            if ($q == 0) {
                continue;
            }
            if (isset( $this->_supported[$type] )) {
                $this->_result[$type] = $q;
            }
        }
        
        if ( count($this->_result) == 0 ) {
            // If no match use first supported
            reset($this->_supported);
            return key($this->_supported);
        } else {
            // Else return match with highest q-value
            reset($this->_result);
            return key($this->_result);
        }
    }


    /**
     * Parses an accept string.
     * @param string $accept
     * @return array Returns an array with the accept types as keys, and
     * q-values as value (float).
     */
    static public function parseRawAccept($accept)
    {
        assert('is_string($accept)');

        // Loop the exploded string parsing names and q-values
        $parsed = array();
        foreach (explode(',', $accept) as $type) {
            $type = explode(';', $type);
            $name = array_shift($type);
            $q = 1.0;
            
            // Loop the rest of $type trying to read q-values
            $qValueRegexp = '/^\s*q\s*=\s*([0-9.]+)\s*$/';
            foreach ($type as $param) {
                if (preg_match($qValueRegexp, $param, $matches)) {
                    $q = floatval($matches[1]);
                }
            }
            
            // Save result
            $parsed[$name] = $q;
        }
        
        return $parsed;
    }


    /**
     * Merge region information in parsed language accept string
     *
     * Takes an array representing a language accept string. Parses
     * the keys so that region information is ignored (eg. 'en' and
     * 'en-US' are treated as the same). The highest q-value for a
     * language is preserved.
     *
     * @param array $values
     * @return array
     */
    static public function mergeRegion(array $values)
    {
        $arrReturn = array();
        foreach ($values as $key => $val) {
            if (strpos($key, '-')) {
                list($lang) = explode('-', $key);
            } else {
                $lang = $key;
            }
            if (
                !array_key_exists($lang, $arrReturn)
                || $arrReturn[$lang] < $val
            ) {
                $arrReturn[$lang] = $val;
            }
        }
        return $arrReturn;
    }

}
