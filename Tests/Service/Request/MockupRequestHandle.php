<?php

namespace Coral\CoreBundle\Tests\Service\Request;

use Coral\CoreBundle\Exception\ConnectorException;
use Coral\CoreBundle\Service\Request\RequestHandleInterface;

/**
 * Curl implementation of Request Interface
 */
class MockupRequestHandle implements RequestHandleInterface
{
    private $url                = null;
    private $method             = null;
    private $responseHeaderSize = null;
    private $responseCode       = null;

    public function __construct($method, $url)
    {
        $this->url = $url;
        $this->method   = $method;
    }

    public function setPayload($payload)
    {
        return true;
    }

    public function setHeader($key, $value)
    {
        return true;
    }

    /**
     * Execute request
     *
     * @return string Response
     */
    public function execute()
    {
        if(!file_exists($this->url))
        {
            throw new ConnectorException('Unable to find file: ' . $this->url);
        }

        $content = file_get_contents($this->url);

        if(false === $content)
        {
            throw new ConnectorException('Unable to load file: ' . $this->url);
        }

        $emptyLines = false;
        if(false === ($emptyLines = strpos($content, "\n\n")))
        {
            if(false === ($emptyLines = strpos($content, "\r\n\r\n")))
            {
                throw new ConnectorException('Unable to find end of headers in: ' . $this->url);
            }
        }
        $responseCode = 200;
        if(preg_match('/HTTP\/1\.1\s*([0-9]{3})\s*OK/i', $content, $matches))
        {
            $responseCode = $matches[1];
        }

        $this->responseHeaderSize = $emptyLines;
        $this->responseCode       = $responseCode;

        return str_replace('{{RANDOM}}', substr(str_shuffle(MD5(microtime())), 0, 10), $content);
    }

    /**
     * Get response code
     *
     * @return int
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * Response headers
     *
     * @return int
     */
    public function getResponseHeaderSize()
    {
        return $this->responseHeaderSize;
    }

    /**
     * Get request method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Get request url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Generate unique request hash
     *
     * @return string
     */
    public function hash()
    {
        return sha1($this->url . '|' . $this->method);
    }
}