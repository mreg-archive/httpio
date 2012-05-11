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
use DateTime;


/**
 * Http Request object
 * @uses Laundromat
 * @package httpio
 *
 * @example
 * // Create Request object from normal PHP sources
 * new Request(
 *      $_SERVER['REMOTE_ADDR'],
 *      $_SERVER['REQUEST_URI'],
 *      $_SERVER['REQUEST_METHOD'],
 *      getallheaders(),
 *      $_COOKIE,
 *      $_GET,
 *      $_POST,
 *      $_FILES
 * );
 *
 * @example
 * // Negotiate content type
 * $n = new Negotiator(array(
 *      'text/html'=>1.0,
 *      'application/xhtml+xml'=>1.0
 * ));
 * $ctype = $n->negotiate(
 *      $request->headers->get('Accept', '/^[a-zA-Z\/+,;=.*() 0-9-]+$/')
 * );
 */
class Request
{

    /**
     * List of valid HTTP methods
     * @var array $VALID_METHODS
     */
    static private $VALID_METHODS = array(
        'HEAD', 'GET', 'POST', 'PUT', 'DELETE',
        'TRACE', 'OPTIONS', 'CONNECT', 'PATCH'
    );


    /**
     * Remote request ip address
     * @var string $ip
     */
    private $ip;


    /**
     * Request uri Filled in __construct().
     * @var string $uri
     */
    private $uri;


    /**
     * Request method. Filled in __construct().
     * @var string $method
     */
    private $method;


    /**
     * Request content type
     * @var string $contentType
     */
    private $contentType = 'text/plain';


    /**
     * Request charset
     * @var string $charset
     */
    private $charset = '';


    /**
     * Request headers Laundromat object
     * @var Laundromat $headers
     */
    public $headers;


    /**
     * Request cookies Laundromat object
     * @var Laundromat $cookies
     */
    public $cookies;


    /**
     * Request query Laundromat object
     * @var Laundromat $query
     */
    public $query;


    /**
     * Request body Laundromat object
     * @var Laundromat $body
     */
    public $body;


    /**
     * Uploaded files info
     * @var array $files
     */
    private $files;


    /**
     * Set values at contruct
     * @param string $ip
     * @param string $uri
     * @param string $method
     * @param array $headers
     * @param array $cookies
     * @param array $query
     * @param array $body
     * @param array $files
     * @throws Exception If request method is uknown
     */
    public function __construct(
        $ip,
        $uri,
        $method,
        array $headers,
        array $cookies,
        array $query,
        array $body,
        array $files
    ) {
        assert('is_string($ip)');
        assert('is_string($uri)');
        assert('is_string($method)');
        $this->ip = $ip;
        $this->uri = $uri;
        $this->method = $method;
        
        // Validate request method
        if ( !in_array($method, self::$VALID_METHODS) ) {
            throw new Exception("Unknown request method '$method'", 501);
        }

        $this->headers = new Laundromat($headers);
        $this->cookies = new Laundromat($cookies);
        $this->query = new Laundromat($query);
        $this->body = new Laundromat($body);
        $this->files = $files;

        // Set content type and charset
        if ( $this->headers->is('Content-Type') ) {
            $ctype = new HeaderParam(
                $this->headers->get('Content-Type', '/^[a-zA-Z\/+,;=.*() 0-9-]+$/')
            );
            $this->contentType = $ctype->getBase();
            $this->charset = $ctype->getParam('charset');
        }
    }


    /**
     * Get remote user ip address.
     *
     * NOTE: Tecnically the ip address is not part of the http protocol. It is
     * still saved for logging purposes.
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }


    /**
     * Get request uri
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }


    /**
     * Get request method
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }


    /**
     * Get request content type
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }


    /**
     * Get request charset
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }


    /**
     * Match ETag against client If-Match header
     * @param string $etag
     * @param string $checkHeader Header to check, 'If-Match' or 'If-None-Match'
     * @return bool TRUE if $etag matches, FALSE otherwise
     */
    public function matchEtag($etag, $checkHeader = "If-Match"){
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
     * @param DateTime $time Current time if omitted
     * @param string $checkHeader 'If-Modified-Since' or 'If-Unmodified-Since'
     * @return bool TRUE if time is earlier than header, FALSE otherwise
     */
    public function matchModified(
        DateTime $time = NULL,
        $checkHeader = 'If-Modified-Since'
    ) {
        if ( !$time ) $time = new DateTime();
        assert('$checkHeader=="If-Modified-Since" || $checkHeader=="If-Unmodified-Since"');

        $headerTime = -1;
        if ( $this->headers->is($checkHeader) ) {
            $headerTime = strtotime(
                $this->headers->get($checkHeader, '/^[a-zA-Z0-9 ,:+-]*$/')
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
     * @return bool
     */
    public function isUpload()
    {
        return !empty($this->files);
    }


    /**
     * Get next upload
     * @return Upload NULL if no more uploadeds exist
     * @throws FileUploadException if there was an file upload error
     */
    public function getNextUpload()
    {
        if ( !$this->isUpload() ) return NULL;

        // Get data and apply defualts
        $data = array_merge(array(
            'name' => '',
            'type' => '',
            'size' => 0,
            'tmp_name' => '',
            'error' => UPLOAD_ERR_OK,
        ), (array)array_shift($this->files));

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
