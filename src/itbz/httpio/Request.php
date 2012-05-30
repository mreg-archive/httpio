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
use DateTime;


/**
 * Lightweight HTTP Request object
 *
 * @uses Laundromat
 *
 * @package httpio
 */
class Request
{

    /**
     * List of valid HTTP methods
     *
     * @var array
     */
    static private $_validMethods = array(
        'HEAD', 'GET', 'POST', 'PUT', 'DELETE',
        'TRACE', 'OPTIONS', 'CONNECT', 'PATCH'
    );


    /**
     * Remote request ip address
     *
     * @var string
     */
    private $_ip;


    /**
     * Request uri Filled in __construct().
     *
     * @var string
     */
    private $_uri;


    /**
     * Request method. Filled in __construct().
     *
     * @var string
     */
    private $_method;


    /**
     * Request content type
     *
     * @var string
     */
    private $_contentType = 'text/plain';


    /**
     * Request charset
     *
     * @var string
     */
    private $_charset = '';


    /**
     * Request headers Laundromat object
     *
     * @var Laundromat
     */
    public $headers;


    /**
     * Request cookies Laundromat object
     *
     * @var Laundromat
     */
    public $cookies;


    /**
     * Request query Laundromat object
     *
     * @var Laundromat
     */
    public $query;


    /**
     * Request body Laundromat object
     *
     * @var Laundromat
     */
    public $body;


    /**
     * Uploaded files info
     *
     * @var array
     */
    private $_files;


    /**
     * Create Request object from global variables
     *
     * @return Request
     */
    public static function createFromGlobals()
    {
        // Get values
        $get = $_GET;
        $post = $_POST;
        $cookie = $_COOKIE;
        $request = $_REQUEST;
        $files = $_FILES;
        $ip = '';
        $method = 'GET';
        $uri = '';

        // Deflect magic qoutes
        // @codeCoverageIgnoreStart
        if ( get_magic_quotes_gpc() ) {
            $get = array_map('stripslashes', $get);
            $post = array_map('stripslashes', $post);
            $cookie = array_map('stripslashes', $cookie);
            $request = array_map('stripslashes', $request);
        }
        // @codeCoverageIgnoreEnd

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if (isset($_SERVER['REQUEST_METHOD'])) {
            $method = $_SERVER['REQUEST_METHOD'];
        }
        
        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
        }

        // Remove query string from uri
        $uri = parse_url($uri, PHP_URL_PATH);

        return new self(
            $ip,
            $uri,
            $method,
            getallheaders(),
            $cookie,
            $get,
            $post,
            $files
        );
    }


    /**
     * Set values at contruct
     *
     * @param string $ip
     *
     * @param string $uri
     *
     * @param string $method
     *
     * @param array $headers
     *
     * @param array $cookies
     *
     * @param array $query
     *
     * @param array $body
     *
     * @param array $files
     *
     * @throws Exception If request method is uknown
     */
    public function __construct(
        $ip = '',
        $uri = '',
        $method = 'GET',
        array $headers = array(),
        array $cookies = array(),
        array $query = array(),
        array $body = array(),
        array $files = array()
    )
    {
        assert('is_string($ip)');
        assert('is_string($uri)');
        assert('is_string($method)');
        $this->_ip = $ip;
        $this->_uri = $uri;
        $this->_method = $method;
        
        // Validate request method
        if ( !in_array($method, self::$_validMethods) ) {
            throw new Exception("Unknown request method '$method'", 501);
        }

        $this->headers = new Laundromat($headers);
        $this->cookies = new Laundromat($cookies);
        $this->query = new Laundromat($query);
        $this->body = new Laundromat($body);
        $this->_files = $files;

        // Set content type and charset
        if ( $this->headers->is('Content-Type') ) {
            $ctype = new HeaderParam(
                $this->headers->get(
                    'Content-Type',
                    '/^[a-zA-Z\/+,;=.*() 0-9-]+$/'
                )
            );
            $this->_contentType = $ctype->getBase();
            $this->_charset = $ctype->getParam('charset');
        }
    }


    /**
     * Get remote user ip address.
     *
     * Tecnically the ip address is not part of the http protocol. It is
     * still saved for logging purposes.
     *
     * @return string
     */
    public function getIp()
    {
        return $this->_ip;
    }


    /**
     * Get request uri
     *
     * @return string
     */
    public function getUri()
    {
        return $this->_uri;
    }


    /**
     * Get request method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->_method;
    }


    /**
     * Check if request method equals $method
     *
     * @param string $method
     *
     * @return bool
     */
    public function isMethod($method)
    {
        assert('is_string($method)');
        $method = strtoupper($method);
        
        return $this->_method == $method;
    }


    /**
     * Get request content type
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->_contentType;
    }


    /**
     * Get request charset
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->_charset;
    }


    /**
     * Match ETag against client If-Match header
     *
     * @param string $etag
     *
     * @param string $checkHeader Header to check, 'If-Match' or 'If-None-Match'
     *
     * @return bool TRUE if $etag matches, FALSE otherwise
     */
    public function matchEtag($etag, $checkHeader = "If-Match")
    {
        assert('is_string($etag)');
        assert('$checkHeader=="If-Match" || $checkHeader=="If-None-Match"');

        $header = "";
        if ( $this->headers->is($checkHeader) ) {
            $header = $this->headers->get($checkHeader, 'ctype_alnum');
        }
        
        return $etag == $header;
    }


    /**
     * Match client if-modified header
     *
     * @param DateTime $time Current time if omitted
     *
     * @param string $header 'If-Modified-Since' or 'If-Unmodified-Since'
     *
     * @return bool TRUE if time is earlier than header, FALSE otherwise
     */
    public function matchModified(
        DateTime $time = NULL,
        $header = 'If-Modified-Since'
    )
    {
        if ( !$time ) $time = new DateTime();
        assert('$header=="If-Modified-Since"||$header=="If-Unmodified-Since"');

        $headerTime = -1;
        if ( $this->headers->is($header) ) {
            $headerTime = strtotime(
                $this->headers->get($header, '/^[a-zA-Z0-9 ,:+-]*$/')
            );
        }

        return (
            $headerTime !== FALSE
            && $headerTime !== -1
            && $time->getTimestamp() < $headerTime
        );
    }


    /**
     * Check if there are any uploads left unprocessed in request
     *
     * @return bool
     */
    public function isUpload()
    {
        return !empty($this->_files);
    }


    /**
     * Get next upload
     *
     * @return Upload NULL if no more uploadeds exist
     *
     * @throws FileUploadException if there was an file upload error
     */
    public function getNextUpload()
    {
        if ( !$this->isUpload() ) return NULL;

        // Get data and apply defualts
        $data = array_merge(
            array(
                'name' => '',
                'type' => '',
                'size' => 0,
                'tmp_name' => '',
                'error' => UPLOAD_ERR_OK,
            ),
            (array)array_shift($this->_files)
        );

        // Create Upload object
        return new Upload(
            $data['name'],
            $data['tmp_name'],
            $data['size'],
            $data['type'],
            $data['error']
        );
    }

}


if (!function_exists('getallheaders')) {

    /**
     * Fallback if getallheaders does not exist
     *
     * @return array Associative array of request headers
     */
    function getallheaders()
    {
        $headers = array();
        foreach ( $_SERVER as $key => $val ) {
            if ( strpos($key, 'HTTP_') === 0 ) {
                $key = str_replace('HTTP_', '', $key);
                $key = str_replace('_', '-', $key);
                $headers[$key] = $val;
            }
        }
        
        return $headers;
    }
}
