<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Manager\InMemory;

use DateTimeImmutable;
use Trikoder\Bundle\OAuth2Bundle\Manager\AccessTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\AccessTokenInterface;

final class AccessTokenManager implements AccessTokenManagerInterface
{
    /**
     * @var AccessTokenInterface[]
     */
    private $accessTokens = [];

    /**
     * {@inheritdoc}
     */
    public function find(string $identifier): ?AccessTokenInterface
    {
        return $this->accessTokens[$identifier] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function save(AccessTokenInterface $accessToken): void
    {
        $this->accessTokens[$accessToken->getIdentifier()] = $accessToken;
    }

    public function clearExpired(): int
    {
        $count = \count($this->accessTokens);

        $now = new DateTimeImmutable();
        $this->accessTokens = array_filter($this->accessTokens, static function (AccessTokenInterface $accessToken) use ($now): bool {
            return $accessToken->getExpiry() >= $now;
        });

        return $count - \count($this->accessTokens);
    }

    public function clearRevoked(): int
    {
        $count = \count($this->accessTokens);

        $this->accessTokens = array_filter($this->accessTokens, static function (AccessTokenInterface $accessToken): bool {
            return !$accessToken->isRevoked();
        });

        return $count - \count($this->accessTokens);
    }
}
