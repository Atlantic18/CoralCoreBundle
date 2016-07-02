<?php

namespace Coral\CoreBundle\Service\Request;

use Symfony\Component\HttpFoundation\HeaderBag;
use Coral\CoreBundle\Exception\ConnectorException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Response extends SymfonyResponse
{
    /**
     * Create response object
     *
     * @param string $content   Response content
     * @param int    $status    Status code
     * @param string $headers   Headers
     * @codeCoverageIgnore
     */
    public function __construct($rawResponse)
    {
        if(false === ($contentStart = strpos($rawResponse, "\n\n")))
        {
            if(false === ($contentStart = strpos($rawResponse, "\r\n\r\n")))
            {
                throw new ConnectorException('Unable to find end of headers in: ' . $rawResponse);
            }
        }
        $rawHeaders   = trim(substr($rawResponse, 0, $contentStart));
        $content      = trim(substr($rawResponse, $contentStart));
        $headers      = $this->parserHttpHeadersAsArray($rawHeaders);

        parent::__construct($content, $headers['HTTP_STATUS'], $headers);
    }

    /**
     * Response header bag
     *
     * @return \Symfony\Component\HttpFoundation\ResponseHeaderBag
     */
    public function getHeaderBag()
    {
        return $this->headers;
    }

    /**
     * Convert string headers into an array
     *
     * @param  string $rawHeaders
     * @return array HTTP Headers
     */
    protected function parserHttpHeadersAsArray($rawHeaders)
    {
        $headers = array();

        foreach(explode("\n", $rawHeaders) as $i => $line)
        {
            if($i == 0)
            {
                $headers['HTTP_STATUS'] = substr($line, 9, 3);
            }
            else
            {
                list($key, $value) = explode(': ', $line);
                $headers[trim($key)] = trim($value);
            }
        }

        return $headers;
    }
}