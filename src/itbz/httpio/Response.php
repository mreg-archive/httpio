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
 * Http Response obect
 * @package httpio
 */
class Response
{

    /**
     * Http response status
     * @var int $status
     */
    private $status = 200;

    
    /**
     * Wrapper to header manipulation functions
     * @var HeaderTool $headerTool
     */
    private $headerTool;


    /**
     * Set header wrap object
     * @param HeaderTool $headerTool
     */
    public function __construct(HeaderTool $headerTool)
    {
        $this->headerTool = $headerTool;
    }


    /**
     * Set http status code
     * @param int $status
     * @return void
     */
    public function setStatus($status)
    {
        assert('is_int($status)');
        $this->status = $status;
        $desc = self::getStatusDesc($status);
        $this->headerTool->status($status, $desc);
    }


    /**
     * Get http status set using Response::setStatus()
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }


    /**
     * Set header. Replace if header exists.
     * @param string $name
     * @param string $value
     * @return void
     */
    public function setHeader($name, $value)
    {
        assert('is_string($name)');
        assert('is_string($value)');
        $this->headerTool->header($name, $value, TRUE);
    }


    /**
     * Add header. Does not replace existing headers
     * @param string $name
     * @param string $value
     * @return void
     */
    public function addHeader($name, $value)
    {
        assert('is_string($name)');
        assert('is_string($value)');
        $this->headerTool->header($name, $value, FALSE);
    }


    /**
     * Remove previously set header.
     * @param string $header Header name, case-insensitive
     * @return void
     */
    public function removeHeader($header)
    {
        assert('is_string($header)');
        $this->headerTool->header_remove($header);
    }


    /**
     * Get a associative array of all headers
     * @return array
     */
    public function getallheaders()
    {
        $headers = array();
        foreach ( $this->headerTool->headers_list() as $header ) {
            list($key, $val) = preg_split("/:/", $header, 2);
            $key = trim($key);
            $val = trim($val);
            $headers[$key] = $val;
        }
        return $headers;
    }


    /**
     * Return true if header is set
     * @param string $header
     * @return bool
     */
    public function isHeader($header)
    {
        assert('is_string($header)');
        $header = strtolower($header);
        $headers = array_change_key_case($this->getallheaders());
        return isset($headers[$header]);
    }


    /**
     * Get the value of a specific header.
     * @param string $header Case insensitive.
     * @return string
     */
    public function getHeader($header)
    {
        assert('is_string($header)');
        $header = strtolower($header);
        $headers = array_change_key_case($this->getallheaders());
        return isset($headers[$header]) ? $headers[$header] : '';
    }


    /**
     * Get response content type
     * @return string
     */
    public function getContentType()
    {
        $params = new HeaderParam($this->getHeader('Content-Type'));
        return $params->getBase();
    }


    /**
     * Get response charset
     * @return string
     */
    public function getCharset()
    {
        $params = new HeaderParam($this->getHeader('Content-Type'));
        return $params->getParam('charset');
    }


    /**
     * Get response language
     * @return string
     */
    public function getLanguage()
    {
        return $this->getHeader('Content-Language');
    }


    /**
     * Send a warning header with value 199
     * @param string $msg
     * @return void
     */
    public function setWarning($msg)
    {
        assert('is_string($msg)');
        $this->addHeader('Warning', "199 $msg");
    }


    /**
     * Send a warning header with value 299
     * @param string $msg
     * @return void
     */
    public function setPersistentWarning($msg)
    {
        assert('is_string($msg)');
        $this->addHeader('Warning', "299 $msg");
    }


    /**
     * Send a file to user.
     * @param string $data File contents
     * @param string $fname File name to send in Content-Disposition header
     * @param string $ctype Content type, defaults to application/x-download
     * @param string $cdisp Content disposition type. 'attachment' or 'inline',
     * defaults to 'attachment'
     * @return void
     */
    public function send_file(
        $data,
        $fname,
        $ctype = 'application/x-download',
        $cdisp = 'attachment'
    ) {
        assert('is_string($data)');
        assert('is_string($fname)');
        assert('is_string($ctype)');
        assert('$cdisp=="inline" || $cdisp=="attachment"');
        $this->setHeader("Content-Type", $ctype);
        $this->setHeader("Content-Disposition", "$cdisp; filename=$fname");
        echo $data;
    }


    /**
     * Get text description of http status code
     * @param int $status
     * @return string
     */
    static public function getStatusDesc($status)
    {
        assert('is_int($status)');
        switch ( $status ) {
            case 100: return "Continue";
            case 101: return "Switching Protocols";
            case 200: return "OK";
            case 201: return "Created";
            case 202: return "Accepted";
            case 203: return "Non-Authoritative Information";
            case 204: return "No Content";
            case 205: return "Reset Content";
            case 206: return "Partial Content";
            case 300: return "Multiple Choices";
            case 301: return "Moved Permanently";
            case 302: return "Found";
            case 303: return "See Other";
            case 304: return "Not Modified";
            case 305: return "Use Proxy";
            case 307: return "Temporary Redirect";
            case 400: return "Bad Request";
            case 401: return "Unauthorized";
            case 402: return "Payment Required";
            case 403: return "Forbidden";
            case 404: return "Not Found";
            case 405: return "Method Not Allowed";
            case 406: return "Not Acceptable";
            case 407: return "Proxy Authentication Required";
            case 408: return "Request Timeout";
            case 409: return "Conflict";
            case 410: return "Gone";
            case 411: return "Length Required";
            case 412: return "Precondition Failed";
            case 413: return "Request Entity Too Large";
            case 414: return "Request-URI Too Long";
            case 415: return "Unsupported Media Type";
            case 416: return "Requested Range Not Satisfiable";
            case 417: return "Expectation Failed";
            case 500: return "Internal Server Error";
            case 501: return "Not Implemented";
            case 502: return "Bad Gateway";
            case 503: return "Service Unavailable";
            case 504: return "Gateway Timeout";
            case 505: return "HTTP Version Not Supported";
            default: return "";
        }
    }

}
