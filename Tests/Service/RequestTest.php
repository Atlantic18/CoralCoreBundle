<?php

/*
 * This file is part of the Coral package.
 *
 * (c) Frantisek Troster <frantisek.troster@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coral\CoreBundle\Tests\Service;

use Coral\CoreBundle\Service\Request\Request;
use Coral\CoreBundle\Utility\JsonParser;
use Coral\CoreBundle\Test\WebTestCase;

class RequestTest extends WebTestCase
{
    public function testDoRequest()
    {
        $request  = $this->getContainer()->get('coral.connector.request');
        $handle   = $request->createHandle(
            Request::GET,
            $this->getContainer()->getParameter('kernel.root_dir') .
            '/fixtures/coral_connect/v1/node/detail/published/config-logger'
        );
        $response = $request->doRequest($handle);
        $parser   = new JsonParser($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(5, $response->getMaxAge());
        $this->assertEquals('text/html; charset=windows-1250', $response->getHeaderBag()->get('content-type'));
        $this->assertEquals('ok', $parser->getMandatoryParam('status'));
        $this->assertEquals(1, $parser->getMandatoryParam('id'));
    }

    public function testCache()
    {
        $request  = $this->getContainer()->get('coral.connector.request');
        $handle   = $request->createHandle(
            Request::GET,
            $this->getContainer()->getParameter('kernel.root_dir') .
            '/fixtures/coral_connect/v1/node/detail/published/config-logger'
        );
        $response = $request->doRequest($handle);
        $parser   = new JsonParser($response->getContent());

        $randomSlug = $parser->getMandatoryParam('slug');
        //following request should be fetched from cache
        $request  = $this->getContainer()->get('coral.connector.request');
        $handle   = $request->createHandle(
            Request::GET,
            $this->getContainer()->getParameter('kernel.root_dir') .
            '/fixtures/coral_connect/v1/node/detail/published/config-logger'
        );
        $response = $request->doRequest($handle);
        $parser   = new JsonParser($response->getContent());
        $this->assertEquals($randomSlug, $parser->getMandatoryParam('slug'), 'Cached response is correct');

        //wait 8 seconds to make sure cache expired
        sleep(8);
        $request  = $this->getContainer()->get('coral.connector.request');
        $handle   = $request->createHandle(
            Request::GET,
            $this->getContainer()->getParameter('kernel.root_dir') .
            '/fixtures/coral_connect/v1/node/detail/published/config-logger'
        );
        $response = $request->doRequest($handle);
        $parser   = new JsonParser($response->getContent());
        $this->assertNotEquals($randomSlug, $parser->getMandatoryParam('slug'), 'Cached TTL works correctly');
    }

    public function testHttpTrace()
    {
        $request  = $this->getContainer()->get('coral.connector.request');
        $handle   = $request->createHandle(
            Request::GET,
            $this->getContainer()->getParameter('kernel.root_dir') .
            '/fixtures/coral_connect/v1/node/detail/published/response-403'
        );

        try
        {
            $request->doRequest($handle);
        }
        catch(\Coral\CoreBundle\Exception\ConnectorException $exception)
        {
            $this->assertEquals(403, $exception->getHttpTrace()->getCode());
            $this->assertStringEndsWith('/v1/node/detail/published/response-403', $exception->getHttpTrace()->getUri());
            $this->assertContains('Invalid authentication', $exception->getHttpTrace()->getBody());
        }
    }

    /**
     * @expectedException Coral\CoreBundle\Exception\ConnectorException
     */
    public function testInvalidUri()
    {
        $request  = $this->getContainer()->get('coral.connector.request');
        $handle   = $request->createHandle(
            Request::GET,
            $this->getContainer()->getParameter('kernel.root_dir') .
            '/fixtures/coral_stark/v1/node/detail/published/config-logger'
        );
        $response = $request->doRequest($handle);
    }
}