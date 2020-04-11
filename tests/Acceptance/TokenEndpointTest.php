<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use DateTimeImmutable;
use Trikoder\Bundle\OAuth2Bundle\Event\UserResolveEvent;
use Trikoder\Bundle\OAuth2Bundle\Manager\AccessTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\AuthorizationCodeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\RefreshTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ScopeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Tests\Fixtures\FixtureFactory;
use Trikoder\Bundle\OAuth2Bundle\Tests\TestHelper;

final class TokenEndpointTest extends AbstractAcceptanceTest
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

    public function testSuccessfulClientCredentialsRequest(): void
    {
        timecop_freeze(new DateTimeImmutable());

        try {
            $this->client->request('POST', '/token', [
                'client_id' => 'foo',
                'client_secret' => 'secret',
                'grant_type' => 'client_credentials',
            ]);
        } finally {
            timecop_return();
        }

        $response = $this->client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json; charset=UTF-8', $response->headers->get('Content-Type'));

        $jsonResponse = json_decode($response->getContent(), true);

        $this->assertSame('Bearer', $jsonResponse['token_type']);
        $this->assertSame(3600, $jsonResponse['expires_in']);
        $this->assertNotEmpty($jsonResponse['access_token']);
    }

    public function testSuccessfulPasswordRequest(): void
    {
        $this->client
            ->getContainer()
            ->get('event_dispatcher')
            ->addListener('trikoder.oauth2.user_resolve', static function (UserResolveEvent $event): void {
                $event->setUser(FixtureFactory::createUser());
            });

        timecop_freeze(new DateTimeImmutable());

        try {
            $this->client->request('POST', '/token', [
                'client_id' => 'foo',
                'client_secret' => 'secret',
                'grant_type' => 'password',
                'username' => 'user',
                'password' => 'pass',
            ]);
        } finally {
            timecop_return();
        }

        $response = $this->client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json; charset=UTF-8', $response->headers->get('Content-Type'));

        $jsonResponse = json_decode($response->getContent(), true);

        $this->assertSame('Bearer', $jsonResponse['token_type']);
        $this->assertSame(3600, $jsonResponse['expires_in']);
        $this->assertNotEmpty($jsonResponse['access_token']);
        $this->assertNotEmpty($jsonResponse['refresh_token']);
    }

    public function testSuccessfulRefreshTokenRequest(): void
    {
        $refreshToken = $this->client
            ->getContainer()
            ->get(RefreshTokenManagerInterface::class)
            ->find(FixtureFactory::FIXTURE_REFRESH_TOKEN);

        timecop_freeze(new DateTimeImmutable());

        try {
            $this->client->request('POST', '/token', [
                'client_id' => 'foo',
                'client_secret' => 'secret',
                'grant_type' => 'refresh_token',
                'refresh_token' => TestHelper::generateEncryptedPayload($refreshToken),
            ]);
        } finally {
            timecop_return();
        }

        $response = $this->client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json; charset=UTF-8', $response->headers->get('Content-Type'));

        $jsonResponse = json_decode($response->getContent(), true);

        $this->assertSame('Bearer', $jsonResponse['token_type']);
        $this->assertSame(3600, $jsonResponse['expires_in']);
        $this->assertNotEmpty($jsonResponse['access_token']);
        $this->assertNotEmpty($jsonResponse['refresh_token']);
    }

    public function testSuccessfulAuthorizationCodeRequest(): void
    {
        $authCode = $this->client
            ->getContainer()
            ->get(AuthorizationCodeManagerInterface::class)
            ->find(FixtureFactory::FIXTURE_AUTH_CODE);

        timecop_freeze(new DateTimeImmutable());

        try {
            $this->client->request('POST', '/token', [
                'client_id' => 'foo',
                'client_secret' => 'secret',
                'grant_type' => 'authorization_code',
                'redirect_uri' => 'https://example.org/oauth2/redirect-uri',
                'code' => TestHelper::generateEncryptedAuthCodePayload($authCode),
            ]);
        } finally {
            timecop_return();
        }

        $response = $this->client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json; charset=UTF-8', $response->headers->get('Content-Type'));

        $jsonResponse = json_decode($response->getContent(), true);

        $this->assertSame('Bearer', $jsonResponse['token_type']);
        $this->assertSame(3600, $jsonResponse['expires_in']);
        $this->assertNotEmpty($jsonResponse['access_token']);
    }

    public function testSuccessfulAuthorizationCodeRequestWithPublicClient(): void
    {
        $authCode = $this->client
            ->getContainer()
            ->get(AuthorizationCodeManagerInterface::class)
            ->find(FixtureFactory::FIXTURE_AUTH_CODE_PUBLIC_CLIENT);

        timecop_freeze(new DateTimeImmutable());

        try {
            $this->client->request('POST', '/token', [
                'client_id' => FixtureFactory::FIXTURE_PUBLIC_CLIENT,
                'grant_type' => 'authorization_code',
                'redirect_uri' => FixtureFactory::FIXTURE_PUBLIC_CLIENT_REDIRECT_URI,
                'code' => TestHelper::generateEncryptedAuthCodePayload($authCode),
            ]);
        } finally {
            timecop_return();
        }

        $response = $this->client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json; charset=UTF-8', $response->headers->get('Content-Type'));

        $jsonResponse = json_decode($response->getContent(), true);

        $this->assertSame('Bearer', $jsonResponse['token_type']);
        $this->assertSame(3600, $jsonResponse['expires_in']);
        $this->assertNotEmpty($jsonResponse['access_token']);
    }

    public function testFailedTokenRequest(): void
    {
        $this->client->request('POST', '/token');

        $response = $this->client->getResponse();

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));

        $jsonResponse = json_decode($response->getContent(), true);

        $this->assertSame('unsupported_grant_type', $jsonResponse['error']);
        $this->assertSame('The authorization grant type is not supported by the authorization server.', $jsonResponse['message']);
        $this->assertSame('Check that all required parameters have been provided', $jsonResponse['hint']);
    }

    public function testFailedClientCredentialsTokenRequest(): void
    {
        $this->client->request('POST', '/token', [
            'client_id' => 'foo',
            'client_secret' => 'wrong',
            'grant_type' => 'client_credentials',
        ]);

        $response = $this->client->getResponse();

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));

        $jsonResponse = json_decode($response->getContent(), true);

        $this->assertSame('invalid_client', $jsonResponse['error']);
        $this->assertSame('Client authentication failed', $jsonResponse['message']);
    }
}
