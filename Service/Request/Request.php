<?php

namespace Coral\CoreBundle\Service\Request;

use Coral\CoreBundle\Exception\ConnectorException;
use Coral\CoreBundle\Exception\HttpTrace;
use Coral\CoreBundle\Utility\JsonParser;
use Coral\CoreBundle\Service\Request\RequestHandleInterface;

class Request
{
    const GET = 'GET';
    const POST = 'POST';
    const DELETE = 'DELETE';

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

        return new Response($rawResponse);
    }
}