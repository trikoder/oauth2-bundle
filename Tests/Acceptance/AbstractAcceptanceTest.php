<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Trikoder\Bundle\OAuth2Bundle\Tests\TestHelper;

abstract class AbstractAcceptanceTest extends WebTestCase
{
    /**
     * @var Application
     */
    protected $application;

    /**
     * @var Client
     */
    protected $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();

        $this->application = new Application($this->client->getKernel());

        TestHelper::initializeDoctrineSchema($this->application);
    }
}
