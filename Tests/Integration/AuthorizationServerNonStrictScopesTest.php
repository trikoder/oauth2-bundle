<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Integration;

use Trikoder\Bundle\OAuth2Bundle\Model\AccessToken;
use Trikoder\Bundle\OAuth2Bundle\Tests\Fixtures\FixtureFactory;

final class AuthorizationServerNonStrictScopesTest extends AbstractIntegrationTest
{
    protected function setUp($strictScopes = false)
    {
        parent::setUp($strictScopes);
    }

    public function testScopeInheritedFromClientWhenNoneRequested()
    {
        $request = $this->createAuthorizationRequest('foo:secret', [
            'grant_type' => 'client_credentials',
        ]);

        $response = $this->handleAuthorizationRequest($request);

        $accessToken = $this->getAccessToken($response['access_token']);

        // Response assertions.
        $this->assertSame('Bearer', $response['token_type']);
        $this->assertSame(3600, $response['expires_in']);
        $this->assertInstanceOf(AccessToken::class, $accessToken);

        // Make sure the access token is issued for the given client ID.
        $this->assertSame('foo', $accessToken->getClient()->getIdentifier());

        // The access token should have the requested scope.
        $this->assertEquals(
            [
                $this->scopeManager->find(FixtureFactory::FIXTURE_SCOPE_FIRST),
            ],
            $accessToken->getScopes()
        );
    }

    public function testScopeInheritedFromConfigurationWhenNoneRequested()
    {
        $request = $this->createAuthorizationRequest('bar:top_secret', [
            'grant_type' => 'client_credentials',
        ]);

        $response = $this->handleAuthorizationRequest($request);

        $accessToken = $this->getAccessToken($response['access_token']);

        // Response assertions.
        $this->assertSame('Bearer', $response['token_type']);
        $this->assertSame(3600, $response['expires_in']);
        $this->assertInstanceOf(AccessToken::class, $accessToken);

        // Make sure the access token is issued for the given client ID.
        $this->assertSame('bar', $accessToken->getClient()->getIdentifier());

        // The access token should have the requested scope.
        $this->assertEquals(
            [
                $this->scopeManager->find(FixtureFactory::FIXTURE_SCOPE_FIRST),
                $this->scopeManager->find(FixtureFactory::FIXTURE_SCOPE_SECOND),
            ],
            $accessToken->getScopes()
        );
    }
}
