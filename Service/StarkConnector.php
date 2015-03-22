<?php

namespace A18\SkipperBundle\Service;

use Coral\CoreBundle\Exception\ConnectorException;
use Coral\CoreBundle\Utility\JsonParser;
use Doctrine\Common\Cache\Cache;

class StarkConnector implements ConnectorInterface
{
    /**
     * Cache driver
     * @var Cache
     */
    private $_cache;

    /**
     * Coral private key
     * @var string
     */
    private $_key;

    /**
     * Coral host where to connect
     * @var string
     */
    private $_host;

    /**
     * Switch ssl verification off for curl
     *
     * @var boolean
     */
    private $_disableSslVerification = true;

    public function __construct(Cache $cache, $host, $key)
    {
        $this->_cache = $cache;
        $this->_host = $host;
        $this->_key = $key;
    }

    private function doCurlRequest($type, $uri, $data = null)
    {
        $ch = curl_init($this->_host . $uri);

        if(null !== $data)
        {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if($this->_disableSslVerification)
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type:application/json',
            'X-Requested-With: XMLHttpRequest',
            'X-Coral-APIKEY: ' . $this->_key
        ));

        $rawResponse = curl_exec($ch);
        $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if(false === $rawResponse)
        {
            throw new ConnectorException('Unable to connect to Stark backend. Response code: ' . $httpCode);
        }

        $parser = new JsonParser($rawResponse, true);
        if($httpCode < 200 || $httpCode > 299)
        {
            throw new ConnectorException(
                "Error connecting to CORAL backend.
                Uri: $type $uri
                Response code: $httpCode.
                Error: " . $parser->getMandatoryParam('message'));
        }

        return $parser;
    }

    /**
     * Create POST request to CORAL backend
     *
     * @param  string $uri  Service URI
     * @param  array  $data Datat to be sent
     * @return JsonResponse Response
     */
    public function doPostRequest($uri, $data = null)
    {
        return $this->doCurlRequest('POST', $uri, $data);
    }

    /**
     * Create GET request to CORAL backend
     *
     * @param  string $uri  Service URI
     * @return JsonResponse Response
     */
    public function doGetRequest($uri)
    {
        return $this->doCurlRequest('GET', $uri);
    }

    /**
     * Create DELETE request to CORAL backend
     *
     * @param  string $uri  Service URI
     * @return JsonResponse Response
     */
    public function doDeleteRequest($uri)
    {
        return $this->doCurlRequest('DELETE', $uri);
    }
}
