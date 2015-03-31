<?php

namespace Coral\CoreBundle\Service\Request;

use Coral\CoreBundle\Exception\ConnectorException;

/**
 * Curl implementation of Request Interface
 *
 * @codeCoverageIgnore
 */
class CurlRequestHandle implements RequestHandleInterface
{
    private $handle             = null;
    private $headers            = null;
    private $url                = null;
    private $method             = null;
    private $responseHeaderSize = null;
    private $responseCode       = null;

    /**
     * Create new request handle
     *
     * @param string  $method                 Request method
     * @param string  $url                    Request url
     * @param boolean $disableSslVerification Flag whether to ignore ssl verification errors
     */
    public function __construct($method, $url, $disableSslVerification = false)
    {
        $this->handle  = curl_init($url);
        $this->headers = array();
        $this->url     = $url;
        $this->method  = $method;

        curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handle, CURLOPT_HEADER, true);

        if($disableSslVerification)
        {
            curl_setopt($this->handle, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->handle, CURLOPT_SSL_VERIFYHOST, false);
        }
    }

    /**
     * Set POST payload
     *
     * @param string $payload
     */
    public function setPayload($payload)
    {
        curl_setopt($this->handle, CURLOPT_POSTFIELDS, $payload);
    }

    /**
     * Set request header
     *
     * @param string $key   Request header key
     * @param string $value Request header value
     */
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
        curl_setopt($this->handle, CURLOPT_HTTPHEADER, $this->headers);
    }

    /**
     * Execute request
     *
     * @return string Response
     */
    public function execute()
    {
        $rawResponse        = curl_exec($this->handle);
        $this->responseCode = curl_getinfo($this->handle, CURLINFO_HTTP_CODE);

        if(false === $rawResponse)
        {
            curl_close($this->handle);
            throw new ConnectorException('Unable to connect to CORAL backend. Response code: ' . $this->responseCode);
        }

        $this->responseHeaderSize = curl_getinfo($this->handle, CURLINFO_HEADER_SIZE);
        curl_close($this->handle);

        return $rawResponse;
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