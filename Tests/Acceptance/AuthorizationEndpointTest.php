<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use DateTimeImmutable;
use Trikoder\Bundle\OAuth2Bundle\Event\AuthorizationRequestResolveEvent;
use Trikoder\Bundle\OAuth2Bundle\Manager\AccessTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\AuthorizationCodeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\RefreshTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ScopeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\OAuth2Events;
use Trikoder\Bundle\OAuth2Bundle\Tests\Fixtures\FixtureFactory;
use Zend\Diactoros\Response;

final class AuthorizationEndpointTest extends AbstractAcceptanceTest
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

    public function testSuccessfulCodeRequest(): void
    {
        $this->client
            ->getContainer()
            ->get('event_dispatcher')
            ->addListener(OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE, function (AuthorizationRequestResolveEvent $event): void {
                $event->resolveAuthorization(AuthorizationRequestResolveEvent::AUTHORIZATION_APPROVED);
            });

        timecop_freeze(new DateTimeImmutable());

        try {
            $this->client->request(
                'GET',
                '/authorize',
                [
                    'client_id' => FixtureFactory::FIXTURE_CLIENT_FIRST,
                    'response_type' => 'code',
                    'state' => 'foobar',
                ]
            );
        } finally {
            timecop_return();
        }

        $response = $this->client->getResponse();

        $this->assertSame(302, $response->getStatusCode());
        $redirectUri = $response->headers->get('Location');

        $this->assertStringStartsWith(FixtureFactory::FIXTURE_CLIENT_FIRST_REDIRECT_URI, $redirectUri);
        $query = [];
        parse_str(parse_url($redirectUri, PHP_URL_QUERY), $query);
        $this->assertArrayHasKey('code', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertEquals('foobar', $query['state']);
    }

    public function testSuccessfulTokenRequest(): void
    {
        $this->client
            ->getContainer()
            ->get('event_dispatcher')
            ->addListener(OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE, function (AuthorizationRequestResolveEvent $event): void {
                $event->resolveAuthorization(AuthorizationRequestResolveEvent::AUTHORIZATION_APPROVED);
            });

        timecop_freeze(new DateTimeImmutable());

        try {
            $this->client->request(
                'GET',
                '/authorize',
                [
                    'client_id' => FixtureFactory::FIXTURE_CLIENT_FIRST,
                    'response_type' => 'token',
                    'state' => 'foobar',
                ]
            );
        } finally {
            timecop_return();
        }

        $response = $this->client->getResponse();

        $this->assertSame(302, $response->getStatusCode());
        $redirectUri = $response->headers->get('Location');

        $this->assertStringStartsWith(FixtureFactory::FIXTURE_CLIENT_FIRST_REDIRECT_URI, $redirectUri);
        $fragment = [];
        parse_str(parse_url($redirectUri, PHP_URL_FRAGMENT), $fragment);
        $this->assertArrayHasKey('access_token', $fragment);
        $this->assertArrayHasKey('token_type', $fragment);
        $this->assertArrayHasKey('expires_in', $fragment);
        $this->assertArrayHasKey('state', $fragment);
        $this->assertEquals('foobar', $fragment['state']);
    }

    public function testCodeRequestRedirectToResolutionUri(): void
    {
        $this->client
            ->getContainer()
            ->get('event_dispatcher')
            ->addListener(OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE, function (AuthorizationRequestResolveEvent $event): void {
                $response = (new Response())->withStatus(302)->withHeader('Location', '/authorize/consent');
                $event->setResponse($response);
            });

        timecop_freeze(new DateTimeImmutable());

        try {
            $this->client->request(
                'GET',
                '/authorize',
                [
                    'client_id' => FixtureFactory::FIXTURE_CLIENT_FIRST,
                    'response_type' => 'code',
                    'state' => 'foobar',
                    'redirect_uri' => FixtureFactory::FIXTURE_CLIENT_FIRST_REDIRECT_URI,
                    'scope' => FixtureFactory::FIXTURE_SCOPE_FIRST . ' ' . FixtureFactory::FIXTURE_SCOPE_SECOND,
                ]
            );
        } finally {
            timecop_return();
        }

        $response = $this->client->getResponse();

        $this->assertSame(302, $response->getStatusCode());
        $redirectUri = $response->headers->get('Location');
        $this->assertEquals('/authorize/consent', $redirectUri);
    }

    public function testAuthorizationRequestEventIsStoppedAfterSettingAResponse(): void
    {
        $eventDispatcher = $this->client
            ->getContainer()
            ->get('event_dispatcher');
        $eventDispatcher->addListener(OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE, function (AuthorizationRequestResolveEvent $event): void {
            $event->resolveAuthorization(AuthorizationRequestResolveEvent::AUTHORIZATION_APPROVED);
        }, 100);
        $eventDispatcher->addListener(OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE, function (AuthorizationRequestResolveEvent $event): void {
            $response = (new Response())->withStatus(302)->withHeader('Location', '/authorize/consent');
            $event->setResponse($response);
        }, 200);

        timecop_freeze(new DateTimeImmutable());

        try {
            $this->client->request(
                'GET',
                '/authorize',
                [
                    'client_id' => FixtureFactory::FIXTURE_CLIENT_FIRST,
                    'response_type' => 'code',
                    'state' => 'foobar',
                ]
            );
        } finally {
            timecop_return();
        }

        $response = $this->client->getResponse();

        $this->assertSame(302, $response->getStatusCode());
        $redirectUri = $response->headers->get('Location');
        $this->assertEquals('/authorize/consent', $redirectUri);
    }

    public function testAuthorizationRequestEventIsStoppedAfterResolution(): void
    {
        $eventDispatcher = $this->client
            ->getContainer()
            ->get('event_dispatcher');
        $eventDispatcher->addListener(OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE, function (AuthorizationRequestResolveEvent $event): void {
            $event->resolveAuthorization(AuthorizationRequestResolveEvent::AUTHORIZATION_APPROVED);
        }, 200);
        $eventDispatcher->addListener(OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE, function (AuthorizationRequestResolveEvent $event): void {
            $response = (new Response())->withStatus(302)->withHeader('Location', '/authorize/consent');
            $event->setResponse($response);
        }, 100);

        timecop_freeze(new DateTimeImmutable());

        try {
            $this->client->request(
                'GET',
                '/authorize',
                [
                    'client_id' => FixtureFactory::FIXTURE_CLIENT_FIRST,
                    'response_type' => 'code',
                    'state' => 'foobar',
                ]
            );
        } finally {
            timecop_return();
        }

        $response = $this->client->getResponse();

        $this->assertSame(302, $response->getStatusCode());
        $redirectUri = $response->headers->get('Location');

        $this->assertStringStartsWith(FixtureFactory::FIXTURE_CLIENT_FIRST_REDIRECT_URI, $redirectUri);
        $query = [];
        parse_str(parse_url($redirectUri, PHP_URL_QUERY), $query);
        $this->assertArrayHasKey('code', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertEquals('foobar', $query['state']);
    }

    public function testFailedCodeRequestRedirectWithFakedRedirectUri(): void
    {
        $this->client
            ->getContainer()
            ->get('event_dispatcher')
            ->addListener(OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE, function (AuthorizationRequestResolveEvent $event): void {
                $event->resolveAuthorization(AuthorizationRequestResolveEvent::AUTHORIZATION_APPROVED);
            });

        timecop_freeze(new DateTimeImmutable());

        try {
            $this->client->request(
                'GET',
                '/authorize',
                [
                    'client_id' => FixtureFactory::FIXTURE_CLIENT_FIRST,
                    'response_type' => 'code',
                    'state' => 'foobar',
                    'redirect_uri' => 'https://example.org/oauth2/malicious-uri',
                ]
            );
        } finally {
            timecop_return();
        }

        $response = $this->client->getResponse();

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));

        $jsonResponse = json_decode($response->getContent(), true);

        $this->assertSame('invalid_client', $jsonResponse['error']);
        $this->assertSame('Client authentication failed', $jsonResponse['message']);
    }

    public function testFailedAuthorizeRequest(): void
    {
        $this->client->request(
            'GET',
            '/authorize'
        );

        $response = $this->client->getResponse();

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));

        $jsonResponse = json_decode($response->getContent(), true);

        $this->assertSame('unsupported_grant_type', $jsonResponse['error']);
        $this->assertSame('The authorization grant type is not supported by the authorization server.', $jsonResponse['message']);
        $this->assertSame('Check that all required parameters have been provided', $jsonResponse['hint']);
    }
}
