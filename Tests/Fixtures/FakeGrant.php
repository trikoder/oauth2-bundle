<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Fixtures;

use League\OAuth2\Server\Grant\AbstractGrant;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;
use Trikoder\Bundle\OAuth2Bundle\League\AuthorizationServer\GrantTypeInterface;

final class FakeGrant extends AbstractGrant implements GrantTypeInterface
{
    public function getIdentifier(): string
    {
        return 'fake_grant';
    }

    public function respondToAccessTokenRequest(ServerRequestInterface $request, ResponseTypeInterface $responseType, \DateInterval $accessTokenTTL): ResponseTypeInterface
    {
        return new BearerTokenResponse();
    }

    public function getAccessTokenTTL(): ?\DateInterval
    {
        return new \DateInterval('PT5H');
    }
}
