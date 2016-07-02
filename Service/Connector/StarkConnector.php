<?php

namespace Coral\CoreBundle\Service\Connector;

use Coral\CoreBundle\Exception\ConnectorException;
use Coral\CoreBundle\Service\Request\Request;

class StarkConnector extends AbstractConnector
{
    /**
     * Coral private key
     * @var string
     */
    private $key;

    /**
     * Coral host where to connect
     * @var string
     */
    private $host;

    /**
     * Request service
     *
     * @var Request
     */
    private $request;

    public function __construct(Request $request, $host, $key)
    {
        $this->host = $host;
        $this->key = $key;
        $this->request = $request;
    }

    /**
     * Create request to CORAL backend
     *
     * @param  string $method  Method name
     * @param  string $uri     Service URI
     * @param  array  $payload Data to be sent
     * @return JsonResponse Response
     */
    public function doRequest($method, $uri, $data = null)
    {
        $method = strtoupper($method);
        if(
            $method != Request::GET &&
            $method != Request::POST &&
            $method != Request::DELETE
        )
        {
            throw new ConnectorException("Invalid method [$method] for connector.");
        }

        $handle = $this->request->createHandle($method, $this->host . $uri);

        if(null !== $data)
        {
            $handle->setPayload(json_encode($data));
        }

        $handle->setHeader('X-Coral-APIKEY', $this->key);

        return $this->request->doRequestAndParse($handle);
    }
}