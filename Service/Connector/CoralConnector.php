<?php

namespace Coral\CoreBundle\Service\Connector;

use Coral\CoreBundle\Exception\ConnectorException;
use Coral\CoreBundle\Service\Request\Request;

class CoralConnector extends AbstractConnector
{
    /**
     * Coral account
     * @var string
     */
    private $account;

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

    public function __construct(Request $request, $host, $account, $key)
    {
        $this->host = $host;
        $this->account = $account;
        $this->key = $key;
        $this->request = $request;
    }

    /**
     * Create request to CORAL backend
     *
     * @param  string $method  Method name
     * @param  string $uri     Service URI
     * @param  array  $payload Data to be sent
     * @return JsonParser Response
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

        $dtime   = time();
        $handle = $this->request->createHandle($method, $this->host . $uri);

        if(null === $data)
        {
            $signatureSource = $this->key . '|' . $dtime . '|' . $this->host . $uri;
        }
        else
        {
            $payload         = json_encode($data);
            $signatureSource = $this->key . '|' . $dtime . '|' . $this->host . $uri . '|' . $payload;

            $handle->setPayload($payload);
        }

        $handle->setHeader('X-CORAL-SIGN', hash('sha256', $signatureSource));
        $handle->setHeader('X-CORAL-ACCOUNT', $this->account);
        $handle->setHeader('X-CORAL-DTIME', $dtime);

        return $this->request->doJsonRequest($handle);
    }
}