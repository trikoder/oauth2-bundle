<?php

namespace Trikoder\Bundle\OAuth2Bundle\Manager\InMemory;

use Trikoder\Bundle\OAuth2Bundle\Manager\AccessTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\AccessToken;

final class AccessTokenManager implements AccessTokenManagerInterface
{
    /**
     * @var AccessToken[]
     */
    private $accessTokens = [];

    public function find(string $identifier): ?AccessToken
    {
        return $this->accessTokens[$identifier] ?? null;
    }

    public function save(AccessToken $accessToken): void
    {
        $this->accessTokens[$accessToken->getIdentifier()] = $accessToken;
    }
}
