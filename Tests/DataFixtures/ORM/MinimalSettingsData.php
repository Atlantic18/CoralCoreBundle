<?php

namespace Coral\CoreBundle\Tests\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Coral\CoreBundle\Entity\Event;
use Coral\CoreBundle\Entity\Account;
use Coral\CoreBundle\Entity\Client;

class MinimalSettingsData implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $event = new Event();
        $event->setName('add_content');

        $account = new Account();
        $account->setName('test_account');

        $client = new Client();
        $client->setToken('super_secure_shared_password');
        $client->setDescription('functional test accesss');
        $client->setAccount($account);

        $account2 = new Account();
        $account2->setName('test_account2');

        $client2 = new Client();
        $client2->setToken('super_secure_shared_password2');
        $client2->setDescription('functional test accesss (2)');
        $client2->setAccount($account2);

        $manager->persist($event);
        $manager->persist($account);
        $manager->persist($client);
        $manager->persist($account2);
        $manager->persist($client2);
        $manager->flush();
    }
}