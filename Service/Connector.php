<?php

namespace Coral\CoreBundle\Service;

use Coral\CoreBundle\Exception\ConnectorException;

class Connector
{
    /**
     * List of available connectors to coral services
     *
     * @var array
     */
    private $connectors;

    public function __construct()
    {
        $this->connectors = array();
    }

    /**
     * Inject connector via DI
     *
     * @param ConnectorInterface $connector
     * @param string $name
     */
    public function addConnector(ConnectorInterface $connector, $name)
    {
        $this->connectors[$name] = $connector;
    }

    /**
     * Connect to service via connector
     *
     * @param  string $connector Connector name
     * @param  string $method    Request method GET|POST|DELETE
     * @param  string $uri       Request URI
     * @param  array  $payload   Optional payload for POST requests
     * @return Coral\CoreBundle\Utility\JsonParser
     */
    public function connect($connector, $method, $uri, $payload = null)
    {
        if(!array_key_exists($connector, $this->connectors))
        {
            throw new ConnectorException(
                "Connector [$connector] not found. Available connectors: " .
                implode(', ', array_keys($this->connectors)) . '.'
            );
        }

        if(strtolower($method) == 'get')
        {
            return $this->connectors[$connector]->doGetRequest($uri);
        }
        if(strtolower($method) == 'delete')
        {
            return $this->connectors[$connector]->doDeleteRequest($uri);
        }
        if(strtolower($method) == 'post')
        {
            return $this->connectors[$connector]->doPostRequest($uri, $payload);
        }

        throw new ConnectorException("Invalid method [$method] for connector [$connector].");
    }
}