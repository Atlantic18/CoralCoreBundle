<?php

namespace Coral\CoreBundle\Tests\Service\Request;

use Coral\CoreBundle\Service\Request\Request;

class RequestMockup extends Request
{
    /**
     * Create a new curl handle
     *
     * @param  string $method Request method
     * @param  string $url    Request url
     *
     * @return RequestHandleInterface
     */
    public function createHandle($method, $url)
    {
        return new MockupRequestHandle($method, $url);
    }
}