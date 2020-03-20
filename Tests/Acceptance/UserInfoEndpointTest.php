<?php

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use Trikoder\Bundle\OAuth2Bundle\Manager\AccessTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\AuthorizationCodeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\RefreshTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ScopeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Tests\Fixtures\FixtureFactory;
use Trikoder\Bundle\OAuth2Bundle\Tests\TestHelper;

final class UserInfoEndpointTest extends AbstractAcceptanceTest
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

    public function testSuccessfulGetUserInfoRequest()
    {
        $accessToken = $this->client->getContainer()->get(AccessTokenManagerInterface::class)
                ->find(FixtureFactory::FIXTURE_ACCESS_TOKEN_USER_BOUND);

        $this->client->request('GET', '/userinfo', [], [], [
            'HTTP_AUTHORIZATION' => TestHelper::generateJwtToken($accessToken),
        ]);

        $response = $this->client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringStartsWith('application/json', $response->headers->get('Content-Type'));

        $jsonResponse = json_decode($response->getContent(), true);
        $this->assertEquals('user', $jsonResponse['sub']);
    }

    public function testSuccessfulPostUserInfoRequest()
    {
        $accessToken = $this->client->getContainer()->get(AccessTokenManagerInterface::class)
                ->find(FixtureFactory::FIXTURE_ACCESS_TOKEN_USER_BOUND);

        $this->client->request('POST', '/userinfo', [
            'access_token' => TestHelper::generateJwtToken($accessToken),
        ]);

        $response = $this->client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringStartsWith('application/json', $response->headers->get('Content-Type'));

        $jsonResponse = json_decode($response->getContent(), true);
        $this->assertEquals('user', $jsonResponse['sub']);
    }

    public function testUnauthorizedGetUserInfoRequest()
    {
        $accessToken = $this->client->getContainer()->get(AccessTokenManagerInterface::class)
                ->find(FixtureFactory::FIXTURE_ACCESS_TOKEN_EXPIRED);

        $this->client->request('GET', '/userinfo', [], [], [
            'HTTP_AUTHORIZATION' => TestHelper::generateJwtToken($accessToken),
        ]);

        $response = $this->client->getResponse();

        $this->assertSame(401, $response->getStatusCode());
    }

    public function testUnauthorizedPostUserInfoRequest()
    {
        $accessToken = $this->client->getContainer()->get(AccessTokenManagerInterface::class)
                ->find(FixtureFactory::FIXTURE_ACCESS_TOKEN_REVOKED);

        $this->client->request('POST', '/userinfo', [
            'access_token' => TestHelper::generateJwtToken($accessToken),
        ]);

        $response = $this->client->getResponse();

        $this->assertSame(401, $response->getStatusCode());
    }
}
