<?php

namespace Coral\CoreBundle\Exception;
/**
 * Trace object of HTTP Error
 */
class HttpTrace
{
    /* URI of the request
     *
     * @var string
     */
    protected $uri = null;

    /* URI of the request
     *
     * @var string
     */
    protected $code  = null;

    /* URI of the request
     *
     * @var string
     */
    protected $body  = null;

    public function __construct($uri, $code, $body)
    {
        $this->uri  = $uri;
        $this->code = $code;
        $this->body = $body;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getBody()
    {
        return $this->body;
    }
}