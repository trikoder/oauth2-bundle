<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use Trikoder\Bundle\OAuth2Bundle\Manager\AccessTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\AuthorizationCodeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\RefreshTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ScopeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Tests\Fixtures\FixtureFactory;
use Trikoder\Bundle\OAuth2Bundle\Tests\TestHelper;

final class SecurityLayerTest extends AbstractAcceptanceTest
{
    protected function setUp(): void
    {
        parent::setUp();

        FixtureFactory::initializeFixtures(
            $this->client->getContainer()->get(ScopeManagerInterface::class),
            $this->client->getContainer()->get(ClientManagerInterface::class),
            $this->client->getContainer()->get(AccessTokenManagerInterface::class),
            $this->client->getContainer()->get(RefreshTokenManagerInterface::class),
            $this->client->getContainer()->get(AuthorizationCodeManagerInterface::class)
        );
    }

    public function testAuthenticatedGuestRequest(): void
    {
        $accessToken = $this->client
            ->getContainer()
            ->get(AccessTokenManagerInterface::class)
            ->find(FixtureFactory::FIXTURE_ACCESS_TOKEN_PUBLIC);

        $this->client->request('GET', '/security-test', [], [], [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', TestHelper::generateJwtToken($accessToken)),
        ]);

        $response = $this->client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Hello, guest', $response->getContent());
    }

    public function testAuthenticatedGuestScopedRequest(): void
    {
        $accessToken = $this->client
            ->getContainer()
            ->get(AccessTokenManagerInterface::class)
            ->find(FixtureFactory::FIXTURE_ACCESS_TOKEN_WITH_SCOPES);

        $this->client->request('GET', '/security-test-scopes', [], [], [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', TestHelper::generateJwtToken($accessToken)),
        ]);

        $response = $this->client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Only certain scopes should be able to access this action.', $response->getContent());
    }

    public function testAuthenticatedUserRequest(): void
    {
        $accessToken = $this->client
            ->getContainer()
            ->get(AccessTokenManagerInterface::class)
            ->find(FixtureFactory::FIXTURE_ACCESS_TOKEN_USER_BOUND);

        $this->client->request('GET', '/security-test', [], [], [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', TestHelper::generateJwtToken($accessToken)),
        ]);

        $response = $this->client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Hello, user', $response->getContent());
    }

    public function testAuthenticatedUserRolesRequest(): void
    {
        $accessToken = $this->client
            ->getContainer()
            ->get(AccessTokenManagerInterface::class)
            ->find(FixtureFactory::FIXTURE_ACCESS_TOKEN_USER_BOUND_WITH_SCOPES);

        $this->client->request('GET', '/security-test-roles', [], [], [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', TestHelper::generateJwtToken($accessToken)),
        ]);

        $response = $this->client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('These are the roles I have currently assigned: ROLE_OAUTH2_FANCY, ROLE_USER', $response->getContent());
    }

    public function testExpiredRequest(): void
    {
        $accessToken = $this->client
            ->getContainer()
            ->get(AccessTokenManagerInterface::class)
            ->find(FixtureFactory::FIXTURE_ACCESS_TOKEN_EXPIRED);

        $this->client->request('GET', '/security-test', [], [], [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', TestHelper::generateJwtToken($accessToken)),
        ]);

        $response = $this->client->getResponse();

        $this->assertSame(401, $response->getStatusCode());
    }

    public function testRevokedRequest(): void
    {
        $accessToken = $this->client
            ->getContainer()
            ->get(AccessTokenManagerInterface::class)
            ->find(FixtureFactory::FIXTURE_ACCESS_TOKEN_REVOKED);

        $this->client->request('GET', '/security-test', [], [], [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', TestHelper::generateJwtToken($accessToken)),
        ]);

        $response = $this->client->getResponse();

        $this->assertSame(401, $response->getStatusCode());
    }

    public function testInsufficientScopeRequest(): void
    {
        $accessToken = $this->client
            ->getContainer()
            ->get(AccessTokenManagerInterface::class)
            ->find(FixtureFactory::FIXTURE_ACCESS_TOKEN_PUBLIC);

        $this->client->request('GET', '/security-test-scopes', [], [], [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', TestHelper::generateJwtToken($accessToken)),
        ]);

        $response = $this->client->getResponse();

        $this->assertSame(403, $response->getStatusCode());
    }

    public function testInvalidRequest(): void
    {
        $this->client->request('GET', '/security-test');

        $response = $this->client->getResponse();

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('Bearer', $response->headers->get('WWW-Authenticate'));
    }
}
