<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Model;

use DateTime;

class RefreshToken
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var DateTime
     */
    private $expiry;

    /**
     * @var AccessToken
     */
    private $accessToken;

    /**
     * @var bool
     */
    private $revoked = false;

    public function __construct(string $identifier, DateTime $expiry, AccessToken $accessToken)
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

    public function getExpiry(): DateTime
    {
        return $this->expiry;
    }

    public function getAccessToken(): AccessToken
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
