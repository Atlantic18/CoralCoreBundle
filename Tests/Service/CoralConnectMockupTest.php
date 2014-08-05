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

use Coral\CoreBundle\Service\CoralConnectMockup;
use Coral\CoreBundle\Test\WebTestCase;

class CoralConnectMockupTest extends WebTestCase
{
    public function testCoralConnect()
    {
        $connector = $this->getContainer()->get('coral.connect');

        $this->assertTrue($connector instanceof CoralConnectMockup);

        $response = $connector->doGetRequest('/v1/node/detail/published/config-logger');

        $this->assertEquals('ok', $response->getMandatoryParam('status'));
        $this->assertEquals(1, $response->getMandatoryParam('id'));

        $response = $connector->doPostRequest('/v1/node/detail/published/config-logger');

        $this->assertEquals('ok', $response->getMandatoryParam('status'));
        $this->assertEquals(1, $response->getMandatoryParam('id'));

        $response = $connector->doDeleteRequest('/v1/node/detail/published/config-logger');

        $this->assertEquals('ok', $response->getMandatoryParam('status'));
        $this->assertEquals(1, $response->getMandatoryParam('id'));
    }
}