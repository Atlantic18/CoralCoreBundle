<?php

namespace Coral\CoreBundle\Service;

interface ConnectorInterface
{
    /**
     * Create POST request to CORAL backend
     *
     * @param  string $uri  Service URI
     * @param  array  $payload Datat to be sent
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
