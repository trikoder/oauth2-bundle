<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Model;

use DateTimeInterface;

class RefreshToken
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var DateTimeInterface
     */
    private $expiry;

    /**
     * @var AccessToken|null
     */
    private $accessToken;

    /**
     * @var bool
     */
    private $revoked = false;

    public function __construct(string $identifier, DateTimeInterface $expiry, ?AccessToken $accessToken = null)
    {
        $this->identifier = $identifier;
        $this->expiry = $expiry;
        $this->accessToken = $accessToken;
    }

    public function __toString(): string
    {
        return $this->getIdentifier();
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getExpiry(): DateTimeInterface
    {
        return $this->expiry;
    }

    public function getAccessToken(): ?AccessToken
    {
        return $this->accessToken;
    }

    public function isRevoked(): bool
    {
        return $this->revoked;
    }

    public function revoke(): self
    {
        $this->revoked = true;

        return $this;
    }
}
