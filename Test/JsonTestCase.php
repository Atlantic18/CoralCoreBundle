<?php

namespace Coral\CoreBundle\Test;

use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

class JsonTestCase extends WebTestCase
{
    private $isEnabledClientProfiler = false;

    public function enableClientProfilerForNextRequest()
    {
        $this->isEnabledClientProfiler = true;
    }

    private function createCustomClient()
    {
        $client = static::createClient();

        if($this->isEnabledClientProfiler)
        {
            $client->enableProfiler();
            $this->isEnabledClientProfiler = false;
        }

        return $client;
    }

    public function doPostRequest($uri, $bodyContent = null)
    {
        $client = $this->createCustomClient();
        $dtime  = time();

        $crawler = $client->request(
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

        return $client;
    }

    public function doAlternativeAccountPostRequest($uri, $bodyContent = null)
    {
        $client = $this->createCustomClient();
        $dtime  = time();

        $crawler = $client->request(
            'POST',
            $uri,
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'HTTP_X-CORAL-ACCOUNT' => $this->getAlternativeAccountName(),
                'HTTP_X-CORAL-SIGN' => $this->getAlternativeSignature($dtime, 'http://localhost' . $uri, $bodyContent),
                'HTTP_X-CORAL-DTIME' => $dtime
            ),
           $bodyContent
        );

        return $client;
    }

    public function doGetRequest($uri, $bodyContent = null)
    {
        $client = $this->createCustomClient();
        $dtime  = time();

        $crawler = $client->request(
            'GET',
            $uri,
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'HTTP_X-CORAL-ACCOUNT' => $this->getAccountName(),
                'HTTP_X-CORAL-SIGN' => $this->getSignature($dtime, 'http://localhost' . $uri, $bodyContent),
                'HTTP_X-CORAL-DTIME' => $dtime
            )
        );

        return $client;
    }

    public function doAlternativeAccountGetRequest($uri, $bodyContent = null)
    {
        $client = $this->createCustomClient();
        $dtime  = time();

        $crawler = $client->request(
            'GET',
            $uri,
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'HTTP_X-CORAL-ACCOUNT' => $this->getAlternativeAccountName(),
                'HTTP_X-CORAL-SIGN' => $this->getAlternativeSignature($dtime, 'http://localhost' . $uri, $bodyContent),
                'HTTP_X-CORAL-DTIME' => $dtime
            )
        );

        return $client;
    }

    public function doDeleteRequest($uri, $bodyContent = null)
    {
        $client = $this->createCustomClient();
        $dtime  = time();

        $crawler = $client->request(
            'DELETE',
            $uri,
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'HTTP_X-CORAL-ACCOUNT' => $this->getAccountName(),
                'HTTP_X-CORAL-SIGN' => $this->getSignature($dtime, 'http://localhost' . $uri, $bodyContent),
                'HTTP_X-CORAL-DTIME' => $dtime
            )
        );

        return $client;
    }

    public function doAlternativeAccountDeleteRequest($uri, $bodyContent = null)
    {
        $client = $this->createCustomClient();
        $dtime  = time();

        $crawler = $client->request(
            'DELETE',
            $uri,
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'HTTP_X-CORAL-ACCOUNT' => $this->getAlternativeAccountName(),
                'HTTP_X-CORAL-SIGN' => $this->getAlternativeSignature($dtime, 'http://localhost' . $uri, $bodyContent),
                'HTTP_X-CORAL-DTIME' => $dtime
            )
        );

        return $client;
    }

    protected function assertIsJsonResponse($client)
    {
        return $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
    }

    protected function assertIsStatusCode($client, $status)
    {
        return $this->assertEquals(
            $status,
            $client->getResponse()->getStatusCode()
        );
    }

    protected function getAccountName()
    {
        //assigned unique account name
        return 'test_account';
    }

    protected function getSharedKey()
    {
        //private shared key between client and server
        return 'super_secure_shared_password';
    }

    protected function getSignature($dtime, $uri, $bodyContent = null)
    {
        return hash('sha256', $this->getSharedKey() . '|' . $dtime . '|' . $uri . (null === $bodyContent ? '' : '|' . $bodyContent));
    }

    protected function getAlternativeAccountName()
    {
        //assigned unique account name
        return 'test_account2';
    }

    protected function getAlternativeSharedKey()
    {
        //private shared key between client and server
        return 'super_secure_shared_password2';
    }

    protected function getAlternativeSignature($dtime, $uri, $bodyContent = null)
    {
        return hash('sha256', $this->getAlternativeSharedKey() . '|' . $dtime . '|' . $uri . (null === $bodyContent ? '' : '|' . $bodyContent));
    }
}
