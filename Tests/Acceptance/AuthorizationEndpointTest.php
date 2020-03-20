<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use DateTimeImmutable;
use Nyholm\Psr7\Response;
use Trikoder\Bundle\OAuth2Bundle\Event\AuthorizationRequestResolveEvent;
use Trikoder\Bundle\OAuth2Bundle\Manager\AccessTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\AuthorizationCodeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\RefreshTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ScopeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\AuthorizationCode;
use Trikoder\Bundle\OAuth2Bundle\OAuth2Events;
use Trikoder\Bundle\OAuth2Bundle\Tests\Fixtures\FixtureFactory;
use Trikoder\Bundle\OAuth2Bundle\Tests\TestHelper;

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
            ->addListener(OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE, static function (AuthorizationRequestResolveEvent $event): void {
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

    public function testSuccessfulPKCEAuthCodeRequest(): void
    {
        $state = bin2hex(random_bytes(20));
        $codeVerifier = bin2hex(random_bytes(64));
        $codeChallengeMethod = 'S256';

        $codeChallenge = strtr(
            rtrim(base64_encode(hash('sha256', $codeVerifier, true)), '='),
            '+/',
            '-_'
        );

        $this->client
            ->getContainer()
            ->get('event_dispatcher')
            ->addListener(OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE, function (AuthorizationRequestResolveEvent $event) use ($state, $codeChallenge, $codeChallengeMethod): void {
                $this->assertSame($state, $event->getState());
                $this->assertSame($codeChallenge, $event->getCodeChallenge());
                $this->assertSame($codeChallengeMethod, $event->getCodeChallengeMethod());

                $event->resolveAuthorization(AuthorizationRequestResolveEvent::AUTHORIZATION_APPROVED);
            });

        timecop_freeze(new DateTimeImmutable());

        try {
            $this->client->request(
                'GET',
                '/authorize',
                [
                    'client_id' => FixtureFactory::FIXTURE_PUBLIC_CLIENT,
                    'response_type' => 'code',
                    'scope' => '',
                    'state' => $state,
                    'code_challenge' => $codeChallenge,
                    'code_challenge_method' => $codeChallengeMethod,
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
        $this->assertArrayHasKey('state', $query);
        $this->assertSame($state, $query['state']);

        $this->assertArrayHasKey('code', $query);
        $payload = json_decode(TestHelper::decryptPayload($query['code']), true);

        $this->assertArrayHasKey('code_challenge', $payload);
        $this->assertArrayHasKey('code_challenge_method', $payload);
        $this->assertSame($codeChallenge, $payload['code_challenge']);
        $this->assertSame($codeChallengeMethod, $payload['code_challenge_method']);

        /** @var AuthorizationCode|null $authCode */
        $authCode = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(AuthorizationCode::class)
            ->findOneBy(['identifier' => $payload['auth_code_id']]);

        $this->assertInstanceOf(AuthorizationCode::class, $authCode);
        $this->assertSame(FixtureFactory::FIXTURE_PUBLIC_CLIENT, $authCode->getClient()->getIdentifier());
    }

    public function testAuthCodeRequestWithPublicClientWithoutCodeChallengeWhenTheChallengeIsRequiredForPublicClients(): void
    {
        $this->client
            ->getContainer()
            ->get('event_dispatcher')
            ->addListener(OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE, function (AuthorizationRequestResolveEvent $event): void {
                $this->fail('This event should not have been dispatched.');
            });

        timecop_freeze(new DateTimeImmutable());

        try {
            $this->client->request(
                'GET',
                '/authorize',
                [
                    'client_id' => FixtureFactory::FIXTURE_PUBLIC_CLIENT,
                    'response_type' => 'code',
                    'scope' => '',
                    'state' => bin2hex(random_bytes(20)),
                ]
            );
        } finally {
            timecop_return();
        }

        $response = $this->client->getResponse();

        $this->assertSame(400, $response->getStatusCode());

        $this->assertSame('application/json', $response->headers->get('Content-Type'));

        $jsonResponse = json_decode($response->getContent(), true);

        $this->assertSame('invalid_request', $jsonResponse['error']);
        $this->assertSame('The request is missing a required parameter, includes an invalid parameter value, includes a parameter more than once, or is otherwise malformed.', $jsonResponse['message']);
        $this->assertSame('Code challenge must be provided for public clients', $jsonResponse['hint']);
    }

    public function testAuthCodeRequestWithClientWhoIsNotAllowedToMakeARequestWithPlainCodeChallengeMethod(): void
    {
        $state = bin2hex(random_bytes(20));
        $codeVerifier = bin2hex(random_bytes(32));
        $codeChallengeMethod = 'plain';
        $codeChallenge = strtr(rtrim(base64_encode($codeVerifier), '='), '+/', '-_');

        $this->client
            ->getContainer()
            ->get('event_dispatcher')
            ->addListener(OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE, function (AuthorizationRequestResolveEvent $event): void {
                $this->fail('This event should not have been dispatched.');
            });

        timecop_freeze(new DateTimeImmutable());

        try {
            $this->client->request(
                'GET',
                '/authorize',
                [
                    'client_id' => FixtureFactory::FIXTURE_PUBLIC_CLIENT,
                    'response_type' => 'code',
                    'scope' => '',
                    'state' => $state,
                    'code_challenge' => $codeChallenge,
                    'code_challenge_method' => $codeChallengeMethod,
                ]
            );
        } finally {
            timecop_return();
        }

        $response = $this->client->getResponse();

        $this->assertSame(400, $response->getStatusCode());

        $this->assertSame('application/json', $response->headers->get('Content-Type'));

        $jsonResponse = json_decode($response->getContent(), true);

        $this->assertSame('invalid_request', $jsonResponse['error']);
        $this->assertSame('The request is missing a required parameter, includes an invalid parameter value, includes a parameter more than once, or is otherwise malformed.', $jsonResponse['message']);
        $this->assertSame('Plain code challenge method is not allowed for this client', $jsonResponse['hint']);
    }

    public function testAuthCodeRequestWithClientWhoIsAllowedToMakeARequestWithPlainCodeChallengeMethod(): void
    {
        $state = bin2hex(random_bytes(20));
        $codeVerifier = bin2hex(random_bytes(32));
        $codeChallengeMethod = 'plain';
        $codeChallenge = strtr(rtrim(base64_encode($codeVerifier), '='), '+/', '-_');

        $this->client
            ->getContainer()
            ->get('event_dispatcher')
            ->addListener(OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE, function (AuthorizationRequestResolveEvent $event) use ($state, $codeChallenge, $codeChallengeMethod): void {
                $this->assertSame($state, $event->getState());
                $this->assertSame($codeChallenge, $event->getCodeChallenge());
                $this->assertSame($codeChallengeMethod, $event->getCodeChallengeMethod());

                $event->resolveAuthorization(AuthorizationRequestResolveEvent::AUTHORIZATION_APPROVED);
            });

        timecop_freeze(new DateTimeImmutable());

        try {
            $this->client->request(
                'GET',
                '/authorize',
                [
                    'client_id' => FixtureFactory::FIXTURE_PUBLIC_CLIENT_ALLOWED_TO_USE_PLAIN_CHALLENGE_METHOD,
                    'response_type' => 'code',
                    'scope' => '',
                    'state' => $state,
                    'code_challenge' => $codeChallenge,
                    'code_challenge_method' => $codeChallengeMethod,
                ]
            );
        } finally {
            timecop_return();
        }

        $response = $this->client->getResponse();

        $this->assertSame(302, $response->getStatusCode());
        $redirectUri = $response->headers->get('Location');

        $this->assertStringStartsWith(FixtureFactory::FIXTURE_PUBLIC_CLIENT_ALLOWED_TO_USE_PLAIN_CHALLENGE_METHOD_REDIRECT_URI, $redirectUri);
        $query = [];
        parse_str(parse_url($redirectUri, PHP_URL_QUERY), $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertSame($state, $query['state']);

        $this->assertArrayHasKey('code', $query);
        $payload = json_decode(TestHelper::decryptPayload($query['code']), true);

        $this->assertArrayHasKey('code_challenge', $payload);
        $this->assertArrayHasKey('code_challenge_method', $payload);
        $this->assertSame($codeChallenge, $payload['code_challenge']);
        $this->assertSame($codeChallengeMethod, $payload['code_challenge_method']);

        /** @var AuthorizationCode|null $authCode */
        $authCode = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(AuthorizationCode::class)
            ->findOneBy(['identifier' => $payload['auth_code_id']]);

        $this->assertInstanceOf(AuthorizationCode::class, $authCode);
        $this->assertSame(FixtureFactory::FIXTURE_PUBLIC_CLIENT_ALLOWED_TO_USE_PLAIN_CHALLENGE_METHOD, $authCode->getClient()->getIdentifier());
    }

    public function testSuccessfulTokenRequest(): void
    {
        $this->client
            ->getContainer()
            ->get('event_dispatcher')
            ->addListener(OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE, static function (AuthorizationRequestResolveEvent $event): void {
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
            ->addListener(OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE, static function (AuthorizationRequestResolveEvent $event): void {
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
        $eventDispatcher->addListener(OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE, static function (AuthorizationRequestResolveEvent $event): void {
            $event->resolveAuthorization(AuthorizationRequestResolveEvent::AUTHORIZATION_APPROVED);
        }, 100);
        $eventDispatcher->addListener(OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE, static function (AuthorizationRequestResolveEvent $event): void {
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
        $eventDispatcher->addListener(OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE, static function (AuthorizationRequestResolveEvent $event): void {
            $event->resolveAuthorization(AuthorizationRequestResolveEvent::AUTHORIZATION_APPROVED);
        }, 200);
        $eventDispatcher->addListener(OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE, static function (AuthorizationRequestResolveEvent $event): void {
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
            ->addListener(OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE, static function (AuthorizationRequestResolveEvent $event): void {
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
