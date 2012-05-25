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


/**
 * Http Response obect
 *
 * @package httpio
 */
class Response
{

    /**
     * List of valid status codes and descriptions
     *
     * @var array
     */
    static private $_statusCodes = array(
        100 => "Continue",
        101 => "Switching Protocols",
        200 => "OK",
        201 => "Created",
        202 => "Accepted",
        203 => "Non-Authoritative Information",
        204 => "No Content",
        205 => "Reset Content",
        206 => "Partial Content",
        300 => "Multiple Choices",
        301 => "Moved Permanently",
        302 => "Found",
        303 => "See Other",
        304 => "Not Modified",
        305 => "Use Proxy",
        307 => "Temporary Redirect",
        400 => "Bad Request",
        401 => "Unauthorized",
        402 => "Payment Required",
        403 => "Forbidden",
        404 => "Not Found",
        405 => "Method Not Allowed",
        406 => "Not Acceptable",
        407 => "Proxy Authentication Required",
        408 => "Request Timeout",
        409 => "Conflict",
        410 => "Gone",
        411 => "Length Required",
        412 => "Precondition Failed",
        413 => "Request Entity Too Large",
        414 => "Request-URI Too Long",
        415 => "Unsupported Media Type",
        416 => "Requested Range Not Satisfiable",
        417 => "Expectation Failed",
        500 => "Internal Server Error",
        501 => "Not Implemented",
        502 => "Bad Gateway",
        503 => "Service Unavailable",
        504 => "Gateway Timeout",
        505 => "HTTP Version Not Supported",
    );


    /**
     * Http response status
     *
     * @var int
     */
    private $_status = 200;


    /**
     * List of headers
     *
     * @var array
     */
    private $_headers = array();


    /**
     * The response body
     *
     * @var string
     */
    private $_body = '';


    /**
     * Set http status code
     *
     * @param int $status
     *
     * @return void
     */
    public function setStatus($status)
    {
        assert('is_int($status)');
        $this->_status = $status;
    }


    /**
     * Get http status code
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->_status;
    }


    /**
     * Clear response body
     *
     * @return void
     */
    public function clearContent()
    {
        $this->_body = '';
    }


    /**
     * Add content to response body
     *
     * @param string $content
     *
     * @return void
     */
    public function addContent($content)
    {
        $this->_body .= $content;
    }


    /**
     * Replace response body with content
     *
     * @param string $content
     *
     * @return void
     */
    public function setContent($content)
    {
        $this->clearContent();
        $this->addContent($content);
    }


    /**
     * Get response body
     *
     * @return string
     */
    public function getContent()
    {
        return $this->_body;
    }


    /**
     * Set header
     *
     * Existing headers with the same same will be overwritten
     *
     * @param string $name
     *
     * @param string $value
     *
     * @return void
     */
    public function setHeader($name, $value)
    {
        assert('is_string($name)');
        assert('is_string($value)');
        $name = self::toCamelCase($name);
        $this->_headers[$name] = array($value);
    }


    /**
     * Add header
     *
     * Existing headers with the same same will NOT be overwritten
     *
     * @param string $name
     *
     * @param string $value
     *
     * @return void
     */
    public function addHeader($name, $value)
    {
        assert('is_string($name)');
        assert('is_string($value)');
        $name = self::toCamelCase($name);
        if (isset($this->_headers[$name])) {
            $this->_headers[$name][] = $value;
        } else {
            $this->_headers[$name] = array($value);
        }
    }


    /**
     * Get the value of a specific header
     *
     * @param string $name Case-insensitive.
     *
     * @return string
     */
    public function getHeader($name)
    {
        assert('is_string($name)');
        $name = self::toCamelCase($name);
        $value = '';
        if (isset($this->_headers[$name])) {
            $value = implode(', ', $this->_headers[$name]);
        }
        
        return $value;
    }


    /**
     * Return true if header is set
     *
     * @param string $name Name of header, case-insensitive
     *
     * @return bool
     */
    public function isHeader($name)
    {
        assert('is_string($name)');
        $name = self::toCamelCase($name);

        return isset($this->_headers[$name]);
    }


    /**
     * Remove header
     *
     * @param string $name Name of header, case-insensitive
     *
     * @return void
     */
    public function removeHeader($name)
    {
        $name = self::toCamelCase($name);
        unset($this->_headers[$name]);
    }


    /**
     * Get array of headers set
     *
     * @return array
     */
    public function getHeaders()
    {
        $headers = array();
        foreach ($this->_headers as $name => $values) {
            foreach ($values as $value) {
                $headers[] = "$name: $value";
            }
        }
        
        return $headers;
    }


    /**
     * Add a warning header
     *
     * @param string $msg
     *
     * @return void
     */
    public function addWarning($msg)
    {
        assert('is_string($msg)');
        $this->addHeader('Warning', "199 $msg");
    }


    /**
     * Add a persistent warning header
     *
     * @param string $msg
     *
     * @return void
     */
    public function addPersistentWarning($msg)
    {
        assert('is_string($msg)');
        $this->addHeader('Warning', "299 $msg");
    }


    /**
     * Send a file to user.
     *
     * @param string $data File contents
     *
     * @param string $fname File name to send in Content-Disposition header
     *
     * @param string $ctype Content type, defaults to application/x-download
     *
     * @param string $cdisp Content disposition type. 'attachment' or 'inline',
     * defaults to 'attachment'
     *
     * @return void
     */
    public function setFile(
        $data,
        $fname,
        $ctype = 'application/x-download',
        $cdisp = 'attachment'
    )
    {
        assert('is_string($data)');
        assert('is_string($fname)');
        assert('is_string($ctype)');
        assert('$cdisp=="inline" || $cdisp=="attachment"');
        $this->setHeader("Content-Type", $ctype);
        $this->setHeader("Content-Disposition", "$cdisp; filename=$fname");
        $this->setContent($data);
    }


    /**
     * Send response
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    public function send()
    {
        // Send status header
        $desc = self::getStatusDesc($this->_status);
        if (strpos(PHP_SAPI, 'fcgi') !== FALSE) {
            header("Status: {$this->_status} $desc");
        } else {
            header("HTTP/1.1 {$this->_status} $desc");
        }
        
        // Send headers
        foreach ($this->getHeaders() as $header) {
            header($header, FALSE);
        }
        
        // Send content
        echo $this->_body;
    }


    /**
     * Convert string to camel case
     *
     * @param string $str
     *
     * @return string
     */
    public static function toCamelCase($str)
    {
        return implode(
            '-',
            array_map(
                function($substr){
                    return ucfirst(strtolower($substr));
                },
                explode('-', $str)
            )
        );
    }


    /**
     * Get text description of http status code
     *
     * @param int $code
     *
     * @return string
     */
    public static function getStatusDesc($code)
    {
        $desc = '';
        if (isset(self::$_statusCodes[$code])) {
            $desc = self::$_statusCodes[$code];
        }

        return $desc;
    }

}
