<?php

namespace Coral\CoreBundle\Service\Connector;

use Coral\CoreBundle\Exception\ConnectorException;
use Coral\CoreBundle\Utility\JsonParser;
use Doctrine\Common\Cache\Cache;
use Coral\CoreBundle\Service\Request\RequestHandleInterface;

class RequestCache
{
    /**
     * Cache driver
     * @var cache
     */
    private $cache;
    public function createRequest($method, $url)
    {
        $request = new CurlRequestHandle($method, $url);

        return $request;
    }

    /**
     * Get cached response. Otherwise return false
     *
     * @param  string  $method Request method
     * @param  string  $uri    Request URI
     * @return boolean|JsonParser
     */
    private function isCached($method, $uri)
    {
        if($method != self::GET)
        {
            return false;
        }

        if(null === $this->cache)
        {
            return false;
        }

        if(false !== ($params = $this->_cache->fetch($uri)))
        {
            $parser = new JsonParser;
            $parser->setParams($params);

            return $parser;
        }

        return false;
    }

    public function doRequest($type, $url, $payload = null)
    {
        if(false !== ($response = $this->isCached($type, $uri)))
        {
            return $response;
        }




        curl_setopt($ch, CURLOPT_HTTPHEADER, array(

            'X-CORAL-ACCOUNT: ' . $this->_account,
            'X-CORAL-SIGN: ' . $signature,
            'X-CORAL-DTIME: ' .$dtime
        ));

        $rawResponse = curl_exec($ch);
        $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if(false === $rawResponse)
        {
            curl_close($ch);
            throw new ConnectorException('Unable to connect to CORAL backend. Response code: ' . $httpCode);
        }

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers     = strtolower(substr($rawResponse, 0, $header_size));
        $rawResponse = substr($rawResponse, $header_size);
        curl_close($ch);

        $parser = new JsonParser($rawResponse, true);
        if($httpCode < 200 || $httpCode > 299)
        {
            throw new ConnectorException(
                "Error connecting to CORAL backend.
                Uri: $type $uri
                Response code: $httpCode.
                Error: " . $parser->getMandatoryParam('message'));
        }

        //Save response to cache
        if($type == 'GET')
        {
            $cacheTTL = false;

            //Cache-Control header with max-age
            if(preg_match('/cache\-control\:\s*(private|public),\s*max\-age=([0-9]+)/i', $headers, $matches))
            {
                //Whether it's private or public is in $matches[1]
                $cacheTTL = $matches[2];
            }

            if(false !== $cacheTTL)
            {
                $this->_cache->save($uri, $parser->getParams(), $cacheTTL);
            }
        }

        return $parser;
    }