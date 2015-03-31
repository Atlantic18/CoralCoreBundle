<?php

namespace Coral\CoreBundle\Service;

use Coral\CoreBundle\Exception\ConnectorException;
use Coral\CoreBundle\Service\Connector\ConnectorInterface;

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
     * Long name alias for to
     *
     * @param  string $connector Connector name
     * @return ConnectorInterface
     */
    public function connectTo($connector)
    {
        return $this->to($connector);
    }

    /**
     * Get connector instance
     *
     * @param  string $connector Connector name
     * @return ConnectorInterface
     */
    public function to($connector)
    {
        if(!array_key_exists($connector, $this->connectors))
        {
            throw new ConnectorException(
                "Connector [$connector] not found. Available connectors: " .
                implode(', ', array_keys($this->connectors)) . '.'
            );
        }

        return $this->connectors[$connector];
    }
}