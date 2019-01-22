<?php

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use Trikoder\Bundle\OAuth2Bundle\Manager\AccessTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Tests\Fixtures\FixtureFactory;
use Trikoder\Bundle\OAuth2Bundle\Tests\TestHelper;

final class UserInfoEndpointTest extends AbstractAcceptanceTest
{
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
