<?php

namespace Coral\CoreBundle\Tests\Controller;

use Coral\CoreBundle\Utility\JsonParser;
use Coral\CoreBundle\Test\JsonTestCase;

class ControllerAuthenticationTest extends JsonTestCase
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

    public function testMissingAccount()
    {
        $uri         = '/v1/version';

        $client = static::createClient();
        $client->request(
            'GET',
            $uri,
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            )
        );

        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 401);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $this->assertEquals('failed', $jsonRequest->getMandatoryParam('status'));
        $this->assertRegExp('/X-CORAL-ACCOUNT/', $jsonRequest->getMandatoryParam('message'));
        $this->assertGreaterThanOrEqual(time() - 5, $jsonRequest->getMandatoryParam('timestamp'));
        $this->assertLessThanOrEqual(time() + 5, $jsonRequest->getMandatoryParam('timestamp'));
    }

    public function testMissingSignature()
    {
        $uri         = '/v1/version';

        $client = static::createClient();
        $client->request(
            'GET',
            $uri,
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'HTTP_X-CORAL-ACCOUNT' => $this->getAccountName()
            )
        );

        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 401);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $this->assertEquals('failed', $jsonRequest->getMandatoryParam('status'));
        $this->assertRegExp('/X-CORAL-SIGN/', $jsonRequest->getMandatoryParam('message'));
        $this->assertGreaterThanOrEqual(time() - 5, $jsonRequest->getMandatoryParam('timestamp'));
        $this->assertLessThanOrEqual(time() + 5, $jsonRequest->getMandatoryParam('timestamp'));
    }

    public function testMissingDtime()
    {
        $uri         = '/v1/version';

        $client = static::createClient();
        $client->request(
            'GET',
            $uri,
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'HTTP_X-CORAL-ACCOUNT' => $this->getAccountName(),
                'HTTP_X-CORAL-SIGN' => $this->getSignature(time(), 'http://localhost' . $uri)
            )
        );

        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 401);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $this->assertEquals('failed', $jsonRequest->getMandatoryParam('status'));
        $this->assertRegExp('/X-CORAL-DTIME/', $jsonRequest->getMandatoryParam('message'));
        $this->assertGreaterThanOrEqual(time() - 5, $jsonRequest->getMandatoryParam('timestamp'));
        $this->assertLessThanOrEqual(time() + 5, $jsonRequest->getMandatoryParam('timestamp'));
    }

    public function testInvalidAccount()
    {
        $dtime       = time();
        $uri         = '/v1/version';

        $client = static::createClient();
        $client->request(
            'GET',
            $uri,
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'HTTP_X-CORAL-ACCOUNT' => 'invalid_account',
                'HTTP_X-CORAL-SIGN' => $this->getSignature($dtime, 'http://localhost' . $uri),
                'HTTP_X-CORAL-DTIME' => $dtime
            )
        );

        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 401);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $this->assertEquals('failed', $jsonRequest->getMandatoryParam('status'));
        $this->assertRegExp('/invalid/', $jsonRequest->getMandatoryParam('message'));
        $this->assertGreaterThanOrEqual(time() - 5, $jsonRequest->getMandatoryParam('timestamp'));
        $this->assertLessThanOrEqual(time() + 5, $jsonRequest->getMandatoryParam('timestamp'));
    }

    public function testInvalidDtimeOlder()
    {
        //time older than 5minutes
        $dtime       = time() - 301;
        $uri         = '/v1/version';

        $client = static::createClient();
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
        $this->assertIsStatusCode($client, 401);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $this->assertEquals('failed', $jsonRequest->getMandatoryParam('status'));
        $this->assertRegExp('/DTIME/', $jsonRequest->getMandatoryParam('message'));
        $this->assertGreaterThanOrEqual(time() - 5, $jsonRequest->getMandatoryParam('timestamp'));
        $this->assertLessThanOrEqual(time() + 5, $jsonRequest->getMandatoryParam('timestamp'));
    }

    public function testInvalidDtimeNewer()
    {
        //time newer than 6minutes
        $dtime       = time() + 360;
        $uri         = '/v1/version';

        $client = static::createClient();
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
        $this->assertIsStatusCode($client, 401);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $this->assertEquals('failed', $jsonRequest->getMandatoryParam('status'));
        $this->assertRegExp('/DTIME/', $jsonRequest->getMandatoryParam('message'));
        $this->assertGreaterThanOrEqual(time() - 5, $jsonRequest->getMandatoryParam('timestamp'));
        $this->assertLessThanOrEqual(time() + 5, $jsonRequest->getMandatoryParam('timestamp'));
    }

    public function testInvalidSignature()
    {
        $dtime       = time();
        $uri         = '/v1/version';

        $client = static::createClient();
        $client->request(
            'GET',
            $uri,
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'HTTP_X-CORAL-ACCOUNT' => $this->getAccountName(),
                'HTTP_X-CORAL-SIGN' => 'fake_signature',
                'HTTP_X-CORAL-DTIME' => $dtime
            )
        );

        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 401);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $this->assertEquals('failed', $jsonRequest->getMandatoryParam('status'));
        $this->assertRegExp('/sign/', $jsonRequest->getMandatoryParam('message'));
        $this->assertGreaterThanOrEqual(time() - 5, $jsonRequest->getMandatoryParam('timestamp'));
        $this->assertLessThanOrEqual(time() + 5, $jsonRequest->getMandatoryParam('timestamp'));
    }

    public function testValidAccount()
    {
        $dtime       = time();
        $uri         = '/v1/version';

        $client = static::createClient();
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
    }
}
