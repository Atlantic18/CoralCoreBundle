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

use Coral\CoreBundle\Service\Connector;
use Coral\CoreBundle\Test\WebTestCase;

class ConnectorTest extends WebTestCase
{
    public function testCoralConnect()
    {
        $connector = $this->getContainer()->get('coral.connector');

        $this->assertTrue($connector instanceof Connector);

        $response = $connector->to('coral')->doRequest('get', '/v1/node/detail/published/config-logger');

        $this->assertEquals('ok', $response->getMandatoryParam('status'));
        $this->assertEquals(1, $response->getMandatoryParam('id'));

        $response = $connector->to('stark')->doRequest('get', '/v1/node/detail/published/config-logger');
        $this->assertEquals('ok', $response->getMandatoryParam('status'));
        $this->assertEquals(2, $response->getMandatoryParam('id'));
        $response = $connector->connectTo('stark')->doPostRequest(
            '/v1/node/detail/published/config-logger',
            array('test' => 'value')
        );
        $this->assertEquals('ok', $response->getMandatoryParam('status'));
        $this->assertEquals(2, $response->getMandatoryParam('id'));

        $response = $connector->to('coral')->doRequest(
            'post',
            '/v1/node/detail/published/config-logger',
            array('test' => 'value')
        );
        $this->assertEquals('ok', $response->getMandatoryParam('status'));
        $this->assertEquals(1, $response->getMandatoryParam('id'));

        $response = $connector->to('coral')->doDeleteRequest('/v1/node/detail/published/config-logger');

        $this->assertEquals('ok', $response->getMandatoryParam('status'));
        $this->assertEquals(1, $response->getMandatoryParam('id'));
    }

    public function testCoralCache()
    {
        $connector = $this->getContainer()->get('coral.connector');

        $response = $connector->to('coral')->doGetRequest('/v1/node/detail/published/config-logger');
        $randomSlug = $response->getMandatoryParam('slug');
        //following request should be fetched from cache
        $response = $connector->to('coral')->doGetRequest('/v1/node/detail/published/config-logger');
        $this->assertEquals($randomSlug, $response->getMandatoryParam('slug'), 'Cached response is correct');

        //wait 8 seconds to make sure cache expired
        sleep(8);
        $response = $connector->to('coral')->doGetRequest('/v1/node/detail/published/config-logger');
        $this->assertNotEquals($randomSlug, $response->getMandatoryParam('slug'), 'Cached TTL works correctly');
    }

    public function testCoralUncached()
    {
        $connector = $this->getContainer()->get('coral.connector');

        $response = $connector->to('coral_uncached')->doGetRequest('/v1/node/detail/published/config-logger');
        $randomSlug = $response->getMandatoryParam('slug');
        $response = $connector->to('coral_uncached')->doGetRequest('/v1/node/detail/published/config-logger');
        $this->assertNotEquals($randomSlug, $response->getMandatoryParam('slug'), 'Uncached request works correctly');
    }

    public function testStarkCache()
    {
        $connector = $this->getContainer()->get('coral.connector');

        $response = $connector->to('stark')->doGetRequest('/v1/node/detail/published/config-logger');
        $randomSlug = $response->getMandatoryParam('slug');
        //following request should not be fetched from cache as cache headers don't allow
        $response = $connector->to('stark')->doGetRequest('/v1/node/detail/published/config-logger');
        $this->assertNotEquals($randomSlug, $response->getMandatoryParam('slug'), 'Do note cach headers work correctly');
    }

    /**
     * @expectedException Coral\CoreBundle\Exception\ConnectorException
     */
    public function testInvalidMethodCoral()
    {
        $connector = $this->getContainer()->get('coral.connector');

        $connector->to('coral')->doRequest('invalid', '/v1/node/detail/published/config-logger');
    }

    /**
     * @expectedException Coral\CoreBundle\Exception\ConnectorException
     */
    public function testInvalidMethodStark()
    {
        $connector = $this->getContainer()->get('coral.connector');

        $connector->to('stark')->doRequest('invalid', '/v1/node/detail/published/config-logger');
    }

    public function testConnector()
    {
        $connector = $this->getContainer()->get('coral.connector');

        $this->assertTrue($connector->to('stark') instanceof \Coral\CoreBundle\Service\Connector\StarkConnector);
        $this->assertTrue($connector->connectTo('coral') instanceof \Coral\CoreBundle\Service\Connector\CoralConnector);
    }

    /**
     * @expectedException Coral\CoreBundle\Exception\ConnectorException
     */
    public function testInvalidConnector()
    {
        $connector = $this->getContainer()->get('coral.connector');

        $connector->to('invalid');
    }

    /**
     * @expectedException Coral\CoreBundle\Exception\ConnectorException
     */
    public function testResponseHeaders()
    {
        $connector = $this->getContainer()->get('coral.connector');

        $connector->to('coral')->doPostRequest('/v1/node/detail/published/response-403');
    }

    public function testHttpTrace()
    {
        $connector = $this->getContainer()->get('coral.connector');

        try
        {
            $connector->to('coral')->doPostRequest('/v1/node/detail/published/response-403');
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
        $connector = $this->getContainer()->get('coral.connector');

        $connector->to('stark')->doGetRequest('/v1/node/detail/published/config');
    }
}