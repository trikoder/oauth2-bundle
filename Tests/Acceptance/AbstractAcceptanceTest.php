<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Trikoder\Bundle\OAuth2Bundle\Tests\TestHelper;

abstract class AbstractAcceptanceTest extends WebTestCase
{
    /**
     * @var Application
     */
    protected $application;

    /**
     * @var KernelBrowser
     */
    protected $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();

        $this->application = new Application($this->client->getKernel());

        TestHelper::initializeDoctrineSchema($this->application);

        $connection = $this->client->getContainer()->get('database_connection');
        if ('sqlite' === $connection->getDatabasePlatform()->getName()) {
            // https://www.sqlite.org/foreignkeys.html
            $connection->executeQuery('PRAGMA foreign_keys = ON');
        }
    }
}
