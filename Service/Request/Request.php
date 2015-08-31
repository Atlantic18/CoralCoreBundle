<?php

namespace Coral\CoreBundle\Service\Request;

use Coral\CoreBundle\Exception\ConnectorException;
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
     * @param  string $method Request method
     * @param  string $url    Request url
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

    public function doJsonRequest(RequestHandleInterface $handle)
    {
        if($this->isCacheable($handle) && (false !== ($params = $this->cache->fetch($handle->hash()))))
        {
            $parser = new JsonParser;
            $parser->setParams($params);

            return $parser;
        }

        $handle->setHeader('Content-Type', 'application/json');
        $handle->setHeader('X-Requested-With', 'XMLHttpRequest');

        $rawResponse = $handle->execute();
        $httpCode    = $handle->getResponseCode();
        $header_size = $handle->getResponseHeaderSize();

        $headers     = substr($rawResponse, 0, $header_size);
        $rawResponse = substr($rawResponse, $header_size);
        $parser      = new JsonParser($rawResponse, true);

        if($httpCode < 200 || $httpCode > 299)
        {
            $type = $handle->getMethod();
            $uri  = $handle->getUrl();
            throw new ConnectorException(
                "Error connecting to CORAL backend.
                Uri: $type $uri
                Response code: $httpCode.
                Error: " . $parser->getMandatoryParam('message'));
        }

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
                $this->cache->save($handle->hash(), $parser->getParams(), $cacheTTL);
            }
        }

        return $parser;
    }
}