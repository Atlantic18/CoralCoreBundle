<?php

namespace Coral\CoreBundle\Service;

use Doctrine\Common\Cache\Cache;

interface CoralConnectInterface
{
    public function __construct(Cache $cache, $host, $account, $key);

    /**
     * Create POST request to CORAL backend
     *
     * @param  string $uri  Service URI
     * @param  array  $data Datat to be sent
     * @return JsonResponse Response
     */
    public function doPostRequest($uri, $data = null);

    /**
     * Create GET request to CORAL backend
     *
     * @param  string $uri  Service URI
     * @param  int $ttl seconds for the cache to live
     * @return JsonResponse Response
     */
    public function doGetRequest($uri, $ttl = false);

    /**
     * Create DELETE request to CORAL backend
     *
     * @param  string $uri  Service URI
     * @return JsonResponse Response
     */
    public function doDeleteRequest($uri);
}
