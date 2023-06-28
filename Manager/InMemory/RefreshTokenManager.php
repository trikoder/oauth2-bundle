<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Manager\InMemory;

use DateTimeImmutable;
use Trikoder\Bundle\OAuth2Bundle\Manager\RefreshTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\RefreshTokenInterface;

final class RefreshTokenManager implements RefreshTokenManagerInterface
{
    /**
     * @var RefreshTokenInterface[]
     */
    private $refreshTokens = [];

    /**
     * {@inheritdoc}
     */
    public function find(string $identifier): ?RefreshTokenInterface
    {
        return $this->refreshTokens[$identifier] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function save(RefreshTokenInterface $refreshToken): void
    {
        $this->refreshTokens[$refreshToken->getIdentifier()] = $refreshToken;
    }

    public function clearExpired(): int
    {
        $count = \count($this->refreshTokens);

        $now = new DateTimeImmutable();
        $this->refreshTokens = array_filter($this->refreshTokens, static function (RefreshTokenInterface $refreshToken) use ($now): bool {
            return $refreshToken->getExpiry() >= $now;
        });

        return $count - \count($this->refreshTokens);
    }

    public function clearRevoked(): int
    {
        $count = \count($this->refreshTokens);

        $this->refreshTokens = array_filter($this->refreshTokens, static function (RefreshTokenInterface $refreshToken): bool {
            return !$refreshToken->isRevoked();
        });

        return $count - \count($this->refreshTokens);
    }
}
