<?php

namespace Coral\CoreBundle\Service\Connector;

interface ConnectorInterface
{
    /**
     * Create request to CORAL backend
     *
     * @param  string $method  Method name
     * @param  string $uri     Service URI
     * @param  array  $payload Data to be sent
     * @return JsonResponse Response
     */
    public function doRequest($method, $uri, $payload = null);

    /**
     * Create POST request to CORAL backend
     *
     * @param  string $uri     Service URI
     * @param  array  $payload Data to be sent
     * @return JsonResponse Response
     */
    public function doPostRequest($uri, $payload = null);

    /**
     * Create GET request to CORAL backend. GET requests
     * are cached if response headers allow it.
     *
     * @param  string $uri  Service URI
     * @return JsonResponse Response
     */
    public function doGetRequest($uri);

    /**
     * Create DELETE request to CORAL backend
     *
     * @param  string $uri  Service URI
     * @return JsonResponse Response
     */
    public function doDeleteRequest($uri);
}
