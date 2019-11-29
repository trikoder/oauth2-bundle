<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Model;

use DateTimeInterface;

class RefreshToken extends AbstractToken
{
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
        parent::__construct($identifier, $expiry);
        $this->accessToken = $accessToken;
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
