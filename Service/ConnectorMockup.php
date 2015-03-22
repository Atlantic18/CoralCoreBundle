<?php

namespace Coral\CoreBundle\Service;

use Coral\CoreBundle\Exception\ConnectorException;
use Coral\CoreBundle\Utility\JsonParser;
use Doctrine\Common\Cache\Cache;

class ConnectorMockup implements ConnectorInterface
{
    protected $rootPath;

    public function __construct(Cache $cache, $host, $account, $key)
    {
        //cache not used in mockup
        //account and key are not needed
        //host is actually a path to the files
        $this->rootPath = $host;
    }

    public function readFile($uri)
    {
        $filePath   = $this->rootPath . $uri;

        if(!file_exists($filePath))
        {
            throw new ConnectorException('Unable to find file: ' . $filePath);
        }

        $content = file_get_contents($filePath);

        if(false === $content)
        {
            throw new ConnectorException('Unable to load file: ' . $filePath);
        }

        return new JsonParser($content, true);
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
        return $this->readFile($uri);
    }

    /**
     * Create GET request to CORAL backend
     *
     * @param  string $uri  Service URI
     * @return JsonResponse Response
     */
    public function doGetRequest($uri, $ttl = false)
    {
        return $this->readFile($uri);
    }

    /**
     * Create DELETE request to CORAL backend
     *
     * @param  string $uri  Service URI
     * @return JsonResponse Response
     */
    public function doDeleteRequest($uri)
    {
        return $this->readFile($uri);
    }
}
