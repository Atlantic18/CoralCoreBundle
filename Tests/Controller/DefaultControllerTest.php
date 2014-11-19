<?php

namespace Coral\CoreBundle\Tests\Controller;

use Coral\CoreBundle\Utility\JsonParser;
use Coral\CoreBundle\Test\JsonTestCase;

class DefaultControllerTest extends JsonTestCase
{
    public function __construct()
    {
        /**
         * Initially a database needs to be created or the very first run
         * of phpunit fails. setupBeforeClass couldn't be used as it is static.
         */
        $this->loadFixtures(array(
            'Coral\CoreBundle\Tests\DataFixtures\ORM\MinimalSettingsData'
        ));
    }

    public function testVersion()
    {
        $client = static::createClient();
        $dtime  = time();
        $uri    = '/v1/version';

        $client->request(
            'GET',
            $uri,
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'HTTP_X-CORAL-ACCOUNT' => $this->getAccountName(),
                'HTTP_X-CORAL-SIGN' => $this->getSignature($dtime, 'http://localhost' . $uri),
                'HTTP_X-CORAL-DTIME' => $dtime
            )
        );

        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 200);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $this->assertEquals('ok', $jsonRequest->getMandatoryParam('status'));
        $this->assertEquals('0.9.9', $jsonRequest->getMandatoryParam('version'));
    }

    public function testInvalidVersion()
    {
        $client = static::createClient(array('environment' => 'invalid_version'));

        $dtime  = time();
        $uri    = '/v1/version';

        $client->request(
            'GET',
            $uri,
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'HTTP_X-CORAL-ACCOUNT' => $this->getAccountName(),
                'HTTP_X-CORAL-SIGN' => $this->getSignature($dtime, 'http://localhost' . $uri),
                'HTTP_X-CORAL-DTIME' => $dtime
            )
        );

        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 200);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $this->assertEquals('ok', $jsonRequest->getMandatoryParam('status'));
        $this->assertEquals('N/A', $jsonRequest->getMandatoryParam('version'));
    }
}