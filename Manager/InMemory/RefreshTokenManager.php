<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Manager\InMemory;

use Trikoder\Bundle\OAuth2Bundle\Manager\RefreshTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\RefreshToken;

final class RefreshTokenManager implements RefreshTokenManagerInterface
{
    /**
     * @var RefreshToken[]
     */
    private $refreshTokens = [];

    /**
     * {@inheritdoc}
     */
    public function find(string $identifier): ?RefreshToken
    {
        return $this->refreshTokens[$identifier] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function save(RefreshToken $refreshToken): void
    {
        $this->refreshTokens[$refreshToken->getIdentifier()] = $refreshToken;
    }
}
