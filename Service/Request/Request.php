<?php

namespace Coral\CoreBundle\Service\Request;

use Coral\CoreBundle\Exception\ConnectorException;
use Coral\CoreBundle\Exception\HttpTrace;
use Coral\CoreBundle\Utility\JsonParser;
use Doctrine\Common\Cache\Cache;
use Coral\CoreBundle\Service\Request\RequestHandleInterface;

class Request
{
    /**
     * Cache driver
     * @var cache
     */
    private $cache;

    const GET = 'GET';
    const POST = 'POST';
    const DELETE = 'DELETE';

    public function __construct(Cache $cache = null)
    {
        $this->cache = $cache;
    }

    /**
     * Create a new curl handle
     *
     * @param  string  $method                 Request method
     * @param  string  $url                    Request url
     * @param  boolean $disableSslVerification Disable ssl verfication
     *
     * @return RequestHandleInterface
     * @codeCoverageIgnore
     */
    public function createHandle($method, $url, $disableSslVerification = false)
    {
        return new CurlRequestHandle($method, $url, $disableSslVerification);
    }

    /**
     * Returns true if handle is possible to be cached
     *
     * @param  RequestHandleInterface $handle Request handle
     * @return boolean                        True if can be cached
     */
    private function isCacheable(RequestHandleInterface $handle)
    {
        if(null === $this->cache)
        {
            return false;
        }
        if($handle->getMethod() != self::GET)
        {
            return false;
        }

        return true;
    }

    /**
     * Json request
     *
     * @deprecated since version 0.3, please use doRequest instead
     * @codeCoverageIgnore
     */
    public function doJsonRequest(RequestHandleInterface $handle)
    {
        return $this->doRequestAndParse($handle);
    }

    /**
     * Do request and return response as a Parser instance. Note: if you need
     * response headers use doRequest instead.
     *
     * @param RequestHandleInterface $handle Request handle
     * @param string $contentType ContentType of the request
     *
     * @return Parser
     */
    public function doRequestAndParse(RequestHandleInterface $handle, $contentType = 'application/json')
    {
        if($this->isCacheable($handle) && (false !== ($params = $this->cache->fetch('parser_' . $handle->hash()))))
        {
            $parser = new JsonParser;
            $parser->setParams($params);

            return $parser;
        }

        $handle->setHeader('Content-Type', $contentType);
        $handle->setHeader('X-Requested-With', 'XMLHttpRequest');

        $rawResponse = $handle->execute();
        $httpCode    = $handle->getResponseCode();
        $header_size = $handle->getResponseHeaderSize();

        $headers     = substr($rawResponse, 0, $header_size);
        $rawResponse = substr($rawResponse, $header_size);

        if($httpCode < 200 || $httpCode > 299)
        {
            $type      = $handle->getMethod();
            $uri       = $handle->getUrl();
            $exception = new ConnectorException(
                "Error connecting to:
                Uri: $type $uri
                Response code: $httpCode.
                Error: " . substr($rawResponse, 0, 255));
            $exception->setHttpTrace(new HttpTrace($uri, $httpCode, $rawResponse));
            throw $exception;
        }

        $parser = new JsonParser($rawResponse, true);
        //Save response to cache
        if($this->isCacheable($handle))
        {
            $cacheTTL = false;

            //Cache-Control header with max-age
            if(preg_match('/cache\-control\:\s*(private|public),\s*max\-age=([0-9]+)/i', $headers, $matches))
            {
                //Whether it's private or public is in $matches[1]
                $cacheTTL = $matches[2];
            }

            if($cacheTTL)
            {
                $this->cache->save('parser_' . $handle->hash(), $parser->getParams(), $cacheTTL);
            }
        }

        return $parser;
    }

    /**
     * Do request
     *
     * @param RequestHandleInterface $handle Request handle
     * @param string $contentType ContentType of the request
     *
     * @return Response
     */
    public function doRequest(RequestHandleInterface $handle, $contentType = 'application/json')
    {
        if
        (
            $this->isCacheable($handle)
            &&
            (false !== ($rawResponse = $this->cache->fetch('response_' . $handle->hash())))
        )
        {
            return new Response($rawResponse);
        }

        $handle->setHeader('Content-Type', $contentType);
        $handle->setHeader('X-Requested-With', 'XMLHttpRequest');

        $rawResponse = $handle->execute();
        $httpCode    = $handle->getResponseCode();

        if($httpCode < 200 || $httpCode > 299)
        {
            $type      = $handle->getMethod();
            $uri       = $handle->getUrl();
            $exception = new ConnectorException(
                "Error connecting to:
                Uri: $type $uri
                Response code: $httpCode.
                Error: " . substr($rawResponse, 0, 255));
            $exception->setHttpTrace(new HttpTrace($uri, $httpCode, $rawResponse));
            throw $exception;
        }

        $response = new Response($rawResponse);
        //Save response to cache
        if($this->isCacheable($handle))
        {
            $cacheTTL = $response->getMaxAge();

            if(null !== $cacheTTL)
            {
                $this->cache->save('response_' . $handle->hash(), $rawResponse, $cacheTTL);
            }
        }

        return $response;
    }
}