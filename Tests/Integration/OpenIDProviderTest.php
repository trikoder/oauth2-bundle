<?php

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Integration;

use DateTime;
use Trikoder\Bundle\OAuth2Bundle\Tests\Fixtures\FixtureFactory;
use Trikoder\Bundle\OAuth2Bundle\Tests\TestHelper;

final class OpenIDProviderTest extends AbstractIntegrationTest
{
    public function testSuccessfulIDTokenRequest(): void
    {
        $openIdAuthCode = $this->authCodeManager->find(FixtureFactory::FIXTURE_AUTH_CODE_OPENID);

        $request = $this->createAuthorizationRequest('foo:secret', [
            'grant_type' => 'authorization_code',
            'code' => TestHelper::generateEncryptedAuthCodePayload($openIdAuthCode),
            'redirect_uri' => 'https://example.org/oauth2/redirect-uri',
        ]);

        timecop_freeze(new DateTime());
        $response = $this->handleAuthorizationRequest($request);
        $issuedAtTimestamp = time();
        $expirationTimestamp = strtotime('+3600 sec');
        timecop_return();

        $this->assertArrayHasKey('id_token', $response);
        $idToken = $this->getIdToken($response['id_token']);
        $this->assertSame('http://', $idToken->getClaim('iss'));
        $this->assertSame('user', $idToken->getClaim('sub'));
        $this->assertSame('foo', $idToken->getClaim('aud'));
        $this->assertEquals($expirationTimestamp, $idToken->getClaim('exp'));
        $this->assertEquals($issuedAtTimestamp, $idToken->getClaim('iat'));
    }
}
