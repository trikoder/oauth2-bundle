<?php

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use DateTime;
use Trikoder\Bundle\OAuth2Bundle\Event\AuthorizationRequestResolveEvent;
use Trikoder\Bundle\OAuth2Bundle\OAuth2Events;
use Trikoder\Bundle\OAuth2Bundle\Tests\Fixtures\FixtureFactory;

final class AuthorizationEndpointTest extends AbstractAcceptanceTest
{
    public function testSuccessfulCodeRequest()
    {
        $this->client
            ->getContainer()
            ->get('event_dispatcher')
            ->addListener(OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE, function (AuthorizationRequestResolveEvent $event) {
                $event->resolveAuthorization(AuthorizationRequestResolveEvent::AUTHORIZATION_APPROVED);
            });

        timecop_freeze(new DateTime());

        $this->client->request(
            'GET',
            '/authorize',
            [
                'client_id' => FixtureFactory::FIXTURE_CLIENT_FIRST,
                'response_type' => 'code',
                'state' => 'foobar',
            ]
        );

        timecop_return();

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

    public function testCodeRequestRedirectToDecision()
    {
        $this->client
            ->getContainer()
            ->get('event_dispatcher')
            ->addListener(OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE, function (AuthorizationRequestResolveEvent $event) {
                $event->setDecisionUri('/authorize/decision');
            });

        timecop_freeze(new DateTime());

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

        timecop_return();

        $response = $this->client->getResponse();

        $this->assertSame(302, $response->getStatusCode());
        $redirectUri = $response->headers->get('Location');
        $this->assertEquals('/authorize/decision', $redirectUri);
    }

    public function testFailedAuthorizeRequest()
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
