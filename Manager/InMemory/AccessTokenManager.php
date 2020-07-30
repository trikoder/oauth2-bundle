<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Manager\InMemory;

use DateTimeImmutable;
use Trikoder\Bundle\OAuth2Bundle\Manager\AccessTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\AccessToken;

final class AccessTokenManager implements AccessTokenManagerInterface
{
    /**
     * @var AccessToken[]
     */
    private $accessTokens = [];

    /** @var bool */
    private $disableAccessTokenSaving;

    public function __construct(bool $disableAccessTokenSaving)
    {
        $this->disableAccessTokenSaving = $disableAccessTokenSaving;
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $identifier): ?AccessToken
    {
        if ($this->disableAccessTokenSaving) {
            return null;
        }

        return $this->accessTokens[$identifier] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function save(AccessToken $accessToken): void
    {
        if ($this->disableAccessTokenSaving) {
            return;
        }

        $this->accessTokens[$accessToken->getIdentifier()] = $accessToken;
    }

    public function clearExpired(): int
    {
        if ($this->disableAccessTokenSaving) {
            return 0;
        }

        $count = \count($this->accessTokens);

        $now = new DateTimeImmutable();
        $this->accessTokens = array_filter($this->accessTokens, static function (AccessToken $accessToken) use ($now): bool {
            return $accessToken->getExpiry() >= $now;
        });

        return $count - \count($this->accessTokens);
    }
}
