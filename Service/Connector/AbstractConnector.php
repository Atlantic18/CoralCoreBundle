<?php

namespace Coral\CoreBundle\Service\Connector;

use Coral\CoreBundle\Service\Request\Request;

abstract class AbstractConnector implements ConnectorInterface
{
    /**
     * Create request to CORAL backend
     *
     * @param  string $method  Method name
     * @param  string $uri     Service URI
     * @param  array  $payload Data to be sent
     * @return JsonParser Response
     */
    abstract public function doRequest($method, $uri, $data = null);

    /**
     * Create POST request to CORAL backend
     *
     * @param  string $uri  Service URI
     * @param  array  $data Datat to be sent
     * @return JsonResponse Response
     */
    public function doPostRequest($uri, $data = null)
    {
        return $this->doRequest(Request::POST, $uri, $data);
    }

    /**
     * Create GET request to CORAL backend
     *
     * @param  string $uri  Service URI
     * @param  int $ttl seconds for the cache to live
     * @return JsonResponse Response
     */
    public function doGetRequest($uri)
    {
        return $this->doRequest(Request::GET, $uri);
    }

    /**
     * Create DELETE request to CORAL backend
     *
     * @param  string $uri  Service URI
     * @return JsonResponse Response
     */
    public function doDeleteRequest($uri)
    {
        return $this->doRequest(Request::DELETE, $uri);
    }
}