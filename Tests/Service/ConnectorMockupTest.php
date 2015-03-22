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

class CoralConnectMockupTest extends WebTestCase
{
    public function testCoralConnect()
    {
        $connector = $this->getContainer()->get('coral.connector');

        $this->assertTrue($connector instanceof Connector);

        $response = $connector->connect('coral', 'get', '/v1/node/detail/published/config-logger');

        $this->assertEquals('ok', $response->getMandatoryParam('status'));
        $this->assertEquals(1, $response->getMandatoryParam('id'));

        $response = $connector->connect('stark', 'get', '/v1/node/detail/published/config-logger');

        $this->assertEquals('ok', $response->getMandatoryParam('status'));
        $this->assertEquals(2, $response->getMandatoryParam('id'));

        $response = $connector->connect('coral', 'post', '/v1/node/detail/published/config-logger');

        $this->assertEquals('ok', $response->getMandatoryParam('status'));
        $this->assertEquals(1, $response->getMandatoryParam('id'));

        $response = $connector->connect('coral', 'delete', '/v1/node/detail/published/config-logger');

        $this->assertEquals('ok', $response->getMandatoryParam('status'));
        $this->assertEquals(1, $response->getMandatoryParam('id'));
    }

    /**
     * @expectedException Coral\CoreBundle\Exception\ConnectorException
     */
    public function testInvalidMethod()
    {
        $connector = $this->getContainer()->get('coral.connector');

        $connector->connect('coral', 'invalid', '/v1/node/detail/published/config-logger');
    }

    /**
     * @expectedException Coral\CoreBundle\Exception\ConnectorException
     */
    public function testInvalidUri()
    {
        $connector = $this->getContainer()->get('coral.connector');

        $connector->connect('stark', 'get', '/v1/node/detail/published/config');
    }
}