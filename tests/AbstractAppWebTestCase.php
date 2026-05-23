<?php

namespace App\Tests;

use Doctrine\Bundle\FixturesBundle\Loader\SymfonyFixturesLoader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Base class for functional tests: boots the kernel, recreates a fresh schema
 * and loads fixtures so each test starts from a known state.
 */
abstract class AbstractAppWebTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->client->disableReboot();

        $container = static::getContainer();
        $this->em = $container->get('doctrine')->getManager();

        // Recreate schema fresh for every test (SQLite is fast)
        $schemaTool = new SchemaTool($this->em);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        if (!empty($metadata)) {
            try {
                $schemaTool->dropSchema($metadata);
            } catch (\Throwable) {
            }
            $schemaTool->createSchema($metadata);
        }

        // Load fixtures — instantiate explicitly since the tagged services may
        // not be visible through the test container's fixtures loader.
        $appFixtures = $container->get(\App\DataFixtures\AppFixtures::class);
        $executor = new ORMExecutor($this->em, new ORMPurger($this->em));
        $executor->execute([$appFixtures], false);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->clear();
    }
}
