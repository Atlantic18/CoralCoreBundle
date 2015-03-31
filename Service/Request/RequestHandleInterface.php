<?php

namespace Coral\CoreBundle\Service\Request;

/**
 * Request interface for abstraction of Curl call mainly for testing
 */
interface RequestHandleInterface
{
    /**
     * Set POST payload
     *
     * @param string $payload
     */
    public function setPayload($payload);

    /**
     * Set request header
     *
     * @param string $key   Request header key
     * @param string $value Request header value
     */
    public function setHeader($key, $value);

    /**
     * Execute request
     *
     * @return string Full raw response
     */
    public function execute();

    /**
     * Get response code
     *
     * @return int
     */
    public function getResponseCode();

    /**
     * Lenght of response headers
     *
     * @return int
     */
    public function getResponseHeaderSize();

    /**
     * Get request method
     *
     * @return string
     */
    public function getMethod();

    /**
     * Get request url
     *
     * @return string
     */
    public function getUrl();

    /**
     * Generate unique request hash
     *
     * @return string
     */
    public function hash();
}
