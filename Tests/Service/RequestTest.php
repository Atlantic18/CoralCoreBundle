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
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RequestTest extends KernelTestCase
{
    public function testDoRequest()
    {
        $request  = $this->getContainer()->get('coral.connector.request');
        $handle   = $request->createHandle(
            Request::GET,
            $this->getContainer()->getParameter('kernel.project_dir') .
            '/Tests/Resources/app/fixtures/coral_connect/v1/node/detail/published/config-logger'
        );
        $response = $request->doRequest($handle);
        $parser   = new JsonParser($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(5, $response->getMaxAge());
        $this->assertEquals('text/html; charset=windows-1250', $response->getHeaderBag()->get('content-type'));
        $this->assertEquals('ok', $parser->getMandatoryParam('status'));
        $this->assertEquals(1, $parser->getMandatoryParam('id'));
    }

    public function testHttpTrace()
    {
        $request  = $this->getContainer()->get('coral.connector.request');
        $handle   = $request->createHandle(
            Request::GET,
            $this->getContainer()->getParameter('kernel.project_dir') .
            '/Tests/Resources/app/fixtures/coral_connect/v1/node/detail/published/response-403'
        );

        try
        {
            $request->doRequest($handle);
        }
        catch(\Coral\CoreBundle\Exception\ConnectorException $exception)
        {
            $this->assertEquals(403, $exception->getHttpTrace()->getCode());
            $this->assertStringEndsWith('/v1/node/detail/published/response-403', $exception->getHttpTrace()->getUri());
            $this->assertStringContainsString('Invalid authentication', $exception->getHttpTrace()->getBody());
        }
    }

    public function testInvalidUri()
    {
        $this->expectException('Coral\CoreBundle\Exception\ConnectorException');

        $request  = $this->getContainer()->get('coral.connector.request');
        $handle   = $request->createHandle(
            Request::GET,
            $this->getContainer()->getParameter('kernel.project_dir') .
            '/Tests/Resources/app/fixtures/coral_stark/v1/node/detail/published/config-logger'
        );
        $response = $request->doRequest($handle);
    }
}