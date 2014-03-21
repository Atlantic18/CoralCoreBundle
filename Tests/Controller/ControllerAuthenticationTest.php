<?php

namespace Coral\CoreBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Common\DataFixtures\Loader;

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
        $uri         = '/v1/observer/add';
        $bodyContent = '{ "event": "add_content", "url": "some_url" }';

        $client = static::createClient();
        $client->request(
            'POST',
            $uri,
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ),
            $bodyContent
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
        $uri         = '/v1/observer/add';
        $bodyContent = '{ "event": "add_content", "url": "some_url" }';

        $client = static::createClient();
        $client->request(
            'POST',
            $uri,
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'HTTP_X-CORAL-ACCOUNT' => $this->getAccountName()
            ),
            $bodyContent
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
        $uri         = '/v1/observer/add';
        $bodyContent = '{ "event": "add_content", "url": "some_url" }';

        $client = static::createClient();
        $client->request(
            'POST',
            $uri,
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'HTTP_X-CORAL-ACCOUNT' => $this->getAccountName(),
                'HTTP_X-CORAL-SIGN' => $this->getSignature(time(), 'http://localhost' . $uri, $bodyContent)
            ),
            $bodyContent
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
        $uri         = '/v1/observer/add';
        $bodyContent = '{ "event": "add_content", "url": "some_url" }';
        $dtime       = time();

        $client = static::createClient();
        $client->request(
            'POST',
            $uri,
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'HTTP_X-CORAL-ACCOUNT' => 'invalid_account',
                'HTTP_X-CORAL-SIGN' => $this->getSignature($dtime, 'http://localhost' . $uri, $bodyContent),
                'HTTP_X-CORAL-DTIME' => $dtime
            ),
            $bodyContent
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
        $uri         = '/v1/observer/add';
        $bodyContent = '{ "event": "add_content", "url": "some_url" }';
        //time older than 5minutes
        $dtime       = time() - 301;

        $client = static::createClient();
        $client->request(
            'POST',
            $uri,
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'HTTP_X-CORAL-ACCOUNT' => $this->getAccountName(),
                'HTTP_X-CORAL-SIGN' => $this->getSignature($dtime, 'http://localhost' . $uri, $bodyContent),
                'HTTP_X-CORAL-DTIME' => $dtime
            ),
            $bodyContent
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
        $uri         = '/v1/observer/add';
        $bodyContent = '{ "event": "add_content", "url": "some_url" }';
        //time newer than 6minutes
        $dtime       = time() + 360;

        $client = static::createClient();
        $client->request(
            'POST',
            $uri,
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'HTTP_X-CORAL-ACCOUNT' => $this->getAccountName(),
                'HTTP_X-CORAL-SIGN' => $this->getSignature($dtime, 'http://localhost' . $uri, $bodyContent),
                'HTTP_X-CORAL-DTIME' => $dtime
            ),
            $bodyContent
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
        $uri         = '/v1/observer/add';
        $bodyContent = '{ "event": "add_content", "url": "some_url" }';
        $dtime       = time();

        $client = static::createClient();
        $client->request(
            'POST',
            $uri,
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'HTTP_X-CORAL-ACCOUNT' => $this->getAccountName(),
                'HTTP_X-CORAL-SIGN' => 'fake_signature',
                'HTTP_X-CORAL-DTIME' => $dtime
            ),
            $bodyContent
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
        $uri         = '/v1/observer/add';
        $bodyContent = '{ "event": "add_content", "url": "some_url" }';
        $dtime       = time();

        $client = static::createClient();
        $client->request(
            'POST',
            $uri,
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'HTTP_X-CORAL-ACCOUNT' => $this->getAccountName(),
                'HTTP_X-CORAL-SIGN' => $this->getSignature($dtime, 'http://localhost' . $uri, $bodyContent),
                'HTTP_X-CORAL-DTIME' => $dtime
            ),
            $bodyContent
        );

        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 201);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $this->assertEquals('ok', $jsonRequest->getMandatoryParam('status'));
    }
}
