<?php

namespace Coral\CoreBundle\Tests\Controller;

use Doctrine\Common\DataFixtures\Loader;

use Coral\CoreBundle\Utility\JsonParser;
use Coral\CoreBundle\Test\JsonTestCase;

class ObserverControllerTest extends JsonTestCase
{
    public function testAddEmptyJson()
    {
        $client = $this->doPostRequest('/v1/observer/add');

        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 500);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $this->assertEquals('failed', $jsonRequest->getMandatoryParam('status'));
        $this->assertRegExp('/mandatory/', $jsonRequest->getMandatoryParam('message'));
    }

    public function testAddInvalidJson()
    {
        $client = $this->doPostRequest('/v1/observer/add', '{"id"');

        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 500);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $this->assertEquals('failed', $jsonRequest->getMandatoryParam('status'));
        $this->assertRegExp('/parsing/', $jsonRequest->getMandatoryParam('message'));
    }

    public function testAddMissingParams()
    {
        $client = $this->doPostRequest(
            '/v1/observer/add',
            '{ "url": "http://www.orm-designer.com/flush-content-cache?node=$SLUG$" }'
        );

        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 500);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $this->assertEquals('failed', $jsonRequest->getMandatoryParam('status'));
        $this->assertRegExp('/event/', $jsonRequest->getMandatoryParam('message'));
    }

    public function testAddInvalidEvent()
    {
        $client = $this->doPostRequest(
            '/v1/observer/add',
            '{ "event": "non_existing", "url": "http://www.orm-designer.com/flush-content-cache?node=$SLUG$" }'
        );

        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 404);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $this->assertEquals('failed', $jsonRequest->getMandatoryParam('status'));
        $this->assertRegExp('/found/', $jsonRequest->getMandatoryParam('message'));
    }

    public function testAddAndListEvent()
    {
        $this->loadFixtures(array(
            'Coral\CoreBundle\Tests\DataFixtures\ORM\MinimalSettingsData'
        ));

        $url = "test_" . sha1(rand());

        //Add new Observer
        $client = $this->doPostRequest(
            '/v1/observer/add',
            '{ "event": "add_content", "url": "' . $url . '" }'
        );

        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 201);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $this->assertEquals('ok', $jsonRequest->getMandatoryParam('status'));
        $this->assertGreaterThan(0, $jsonRequest->getMandatoryParam('id'));

        //Add new Observer to alternative account
        $client = $this->doAlternativeAccountPostRequest(
            '/v1/observer/add',
            '{ "event": "add_content", "url": "' . $url . '" }'
        );

        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 201);

        //Validate whether the observer was added correctly
        $client = $this->doGetRequest('/v1/observer/list/add_content');

        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 200);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $this->assertEquals('ok', $jsonRequest->getMandatoryParam('status'));

        $items = $jsonRequest->getMandatoryParam('items');
        $this->assertCount(1, $jsonRequest->getMandatoryParam('items'));
        $this->assertEquals($url, $items[0]['url']);
    }

    public function testUpdate()
    {
        $this->loadFixtures(array(
            'Coral\CoreBundle\Tests\DataFixtures\ORM\MinimalSettingsData'
        ));

        $url = "test_" . sha1(rand());

        //Add new Observer
        $client = $this->doPostRequest(
            '/v1/observer/add',
            '{ "event": "add_content", "url": "' . $url . '" }'
        );

        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 201);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $this->assertEquals('ok', $jsonRequest->getMandatoryParam('status'));
        $this->assertGreaterThan(0, $jsonRequest->getMandatoryParam('id'));

        //Update observer
        $changedUrl = "test_" . sha1(rand());
        $client = $this->doPostRequest(
            '/v1/observer/update/' . $jsonRequest->getMandatoryParam('id'),
            '{ "event": "add_content", "url": "' . $changedUrl . '" }'
        );

        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 200);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());
        $this->assertEquals('ok', $jsonRequest->getMandatoryParam('status'));

        //Validate whether the observer was added correctly
        $client = $this->doGetRequest('/v1/observer/list');

        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 200);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $this->assertEquals('ok', $jsonRequest->getMandatoryParam('status'));

        $items = $jsonRequest->getMandatoryParam('items');
        $this->assertCount(1, $jsonRequest->getMandatoryParam('items'));
        $this->assertEquals($changedUrl, $items[0]['url']);
    }

    public function testUpdateNonExisting()
    {
        $this->loadFixtures(array(
            'Coral\CoreBundle\Tests\DataFixtures\ORM\MinimalSettingsData'
        ));

        $client = $this->doPostRequest(
            '/v1/observer/update/10',
            '{ "event": "add_content", "url": "url" }'
        );

        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 404);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $this->assertEquals('failed', $jsonRequest->getMandatoryParam('status'));
        $this->assertRegExp('/found/', $jsonRequest->getMandatoryParam('message'));
    }

    public function testUpdateInvalidEvent()
    {
        $this->loadFixtures(array(
            'Coral\CoreBundle\Tests\DataFixtures\ORM\MinimalSettingsData'
        ));

        $client = $this->doPostRequest(
            '/v1/observer/add',
            '{ "event": "add_content", "url": "url" }'
        );
        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $client = $this->doPostRequest(
            '/v1/observer/update/' . $jsonRequest->getMandatoryParam('id'),
            '{ "event": "non_existing", "url": "url" }'
        );

        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 404);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $this->assertEquals('failed', $jsonRequest->getMandatoryParam('status'));
        $this->assertRegExp('/found/', $jsonRequest->getMandatoryParam('message'));
    }

    public function testUpdateInvalidAccount()
    {
        $this->loadFixtures(array(
            'Coral\CoreBundle\Tests\DataFixtures\ORM\MinimalSettingsData'
        ));

        $client = $this->doPostRequest(
            '/v1/observer/add',
            '{ "event": "add_content", "url": "url" }'
        );
        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $client = $this->doAlternativeAccountPostRequest(
            '/v1/observer/update/' . $jsonRequest->getMandatoryParam('id'),
            '{ "event": "add_content", "url": "url" }'
        );

        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 401);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $this->assertEquals('failed', $jsonRequest->getMandatoryParam('status'));
        $this->assertRegExp('/invalid/', $jsonRequest->getMandatoryParam('message'));
    }

    public function testList()
    {
        $this->loadFixtures(array(
            'Coral\CoreBundle\Tests\DataFixtures\ORM\MinimalSettingsData'
        ));

        $url = "test_" . sha1(rand());

        //Add new Observer
        $this->doPostRequest(
            '/v1/observer/add',
            '{ "event": "add_content", "url": "' . $url . '" }'
        );
        $this->doAlternativeAccountPostRequest(
            '/v1/observer/add',
            '{ "event": "add_content", "url": "' . $url . '" }'
        );

        //Validate whether the observer was added correctly
        $client = $this->doGetRequest('/v1/observer/list');

        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 200);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $this->assertEquals('ok', $jsonRequest->getMandatoryParam('status'));

        $items = $jsonRequest->getMandatoryParam('items');
        $this->assertCount(1, $jsonRequest->getMandatoryParam('items'));
        $this->assertEquals($url, $items[0]['url']);
    }

    public function testListEventNonExisting()
    {
        $client = $this->doGetRequest('/v1/observer/list/non_existing');

        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 200);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $this->assertEquals('ok', $jsonRequest->getMandatoryParam('status'));
        $this->assertCount(0, $jsonRequest->getMandatoryParam('items'));
    }

    public function testDeleteNonExisting()
    {
        $client = $this->doDeleteRequest('/v1/observer/delete/10');

        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 404);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $this->assertEquals('failed', $jsonRequest->getMandatoryParam('status'));
        $this->assertRegExp('/found/', $jsonRequest->getMandatoryParam('message'));
    }

    public function testDelete()
    {
        $this->loadFixtures(array(
            'Coral\CoreBundle\Tests\DataFixtures\ORM\MinimalSettingsData'
        ));

        $this->doPostRequest(
            '/v1/observer/add',
            '{ "event": "add_content", "url": "url" }'
        );

        //Validate whether the observer was added correctly
        $client = $this->doGetRequest('/v1/observer/list');
        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $items = $jsonRequest->getMandatoryParam('items');
        $this->assertCount(1, $jsonRequest->getMandatoryParam('items'));

        $client = $this->doDeleteRequest('/v1/observer/delete/' . $items[0]['id']);

        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 200);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());
        $this->assertEquals('ok', $jsonRequest->getMandatoryParam('status'));

        $client = $this->doGetRequest('/v1/observer/list');
        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $items = $jsonRequest->getMandatoryParam('items');
        $this->assertCount(0, $jsonRequest->getMandatoryParam('items'));
    }

    public function testDeleteInvalidAccount()
    {
        $this->loadFixtures(array(
            'Coral\CoreBundle\Tests\DataFixtures\ORM\MinimalSettingsData'
        ));

        $client = $this->doPostRequest(
            '/v1/observer/add',
            '{ "event": "add_content", "url": "url" }'
        );
        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $client = $this->doAlternativeAccountDeleteRequest(
            '/v1/observer/delete/' . $jsonRequest->getMandatoryParam('id')
        );

        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 401);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $this->assertEquals('failed', $jsonRequest->getMandatoryParam('status'));
        $this->assertRegExp('/invalid/', $jsonRequest->getMandatoryParam('message'));
    }
}
