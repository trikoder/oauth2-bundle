<?php

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Trikoder\Bundle\OAuth2Bundle\Manager\AccessTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\AuthorizationCodeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\RefreshTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ScopeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Tests\Fixtures\FixtureFactory;
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

    protected function setUp()
    {
        $this->client = self::createClient();

        $this->application = new Application($this->client->getKernel());

        TestHelper::initializeDoctrineSchema($this->application);

        FixtureFactory::initializeFixtures(
            $this->client->getContainer()->get(ScopeManagerInterface::class),
            $this->client->getContainer()->get(ClientManagerInterface::class),
            $this->client->getContainer()->get(AccessTokenManagerInterface::class),
            $this->client->getContainer()->get(RefreshTokenManagerInterface::class),
            $this->client->getContainer()->get(AuthorizationCodeManagerInterface::class)
        );
    }
}
