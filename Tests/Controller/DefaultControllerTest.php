<?php

namespace Coral\CoreBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testVersion()
    {
        $client = static::createClient();
        $client->request('GET', '/v1/version');

        $this->assertEquals('Version: 0.9.9', $client->getResponse()->getContent());
    }

    public function testInvalidVersion()
    {
        $client = static::createClient(array('environment' => 'invalid_version'));
        $client->request('GET', '/v1/version');

        $this->assertEquals('Version: N/A', $client->getResponse()->getContent());
    }
}