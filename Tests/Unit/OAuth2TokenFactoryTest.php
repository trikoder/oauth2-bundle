<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Trikoder\Bundle\OAuth2Bundle\Security\Authentication\Token\OAuth2Token;
use Trikoder\Bundle\OAuth2Bundle\Security\Authentication\Token\OAuth2TokenFactory;
use Trikoder\Bundle\OAuth2Bundle\Tests\Fixtures\FixtureFactory;
use Trikoder\Bundle\OAuth2Bundle\Tests\Fixtures\User;

final class OAuth2TokenFactoryTest extends TestCase
{
    public function testCreatingToken(): void
    {
        $rolePrefix = 'ROLE_OAUTH2_';
        $factory = new OAuth2TokenFactory($rolePrefix);

        $scopes = [FixtureFactory::FIXTURE_SCOPE_FIRST];
        $serverRequest = $this->createMock(ServerRequestInterface::class);
        $serverRequest->expects($this->once())
            ->method('getAttribute')
            ->with('oauth_scopes', [])
            ->willReturn($scopes);

        $user = new User();
        $providerKey = 'main';

        $token = $factory->createOAuth2Token($serverRequest, $user, $providerKey);

        $this->assertInstanceOf(OAuth2Token::class, $token);

        $roles = $token->getRoleNames();
        $this->assertCount(1, $roles);
        $this->assertSame($rolePrefix . strtoupper($scopes[0]), $roles[0]);

        $this->assertFalse($token->isAuthenticated());
        $this->assertSame($user, $token->getUser());
        $this->assertSame($providerKey, $token->getProviderKey());
    }
}
