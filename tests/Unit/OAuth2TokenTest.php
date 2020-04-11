<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Trikoder\Bundle\OAuth2Bundle\Security\Authentication\Token\OAuth2Token;
use Trikoder\Bundle\OAuth2Bundle\Tests\Fixtures\FixtureFactory;
use Trikoder\Bundle\OAuth2Bundle\Tests\Fixtures\User;

final class OAuth2TokenTest extends TestCase
{
    public function testTokenSerialization(): void
    {
        $scopes = [FixtureFactory::FIXTURE_SCOPE_FIRST];
        $serverRequest = $this->createMock(ServerRequestInterface::class);
        $serverRequest->expects($this->once())
            ->method('getAttribute')
            ->with('oauth_scopes', [])
            ->willReturn($scopes);

        $user = new User();
        $rolePrefix = 'ROLE_OAUTH2_';
        $providerKey = 'main';
        $token = new OAuth2Token($serverRequest, $user, $rolePrefix, $providerKey);

        /** @var OAuth2Token $unserializedToken */
        $unserializedToken = unserialize(serialize($token));

        $this->assertSame($providerKey, $unserializedToken->getProviderKey());

        $expectedRole = $rolePrefix . strtoupper($scopes[0]);
        $this->assertSame([$expectedRole], $token->getRoleNames());

        $this->assertSame($user->getUsername(), $unserializedToken->getUser()->getUsername());
        $this->assertFalse($unserializedToken->isAuthenticated());
    }
}
