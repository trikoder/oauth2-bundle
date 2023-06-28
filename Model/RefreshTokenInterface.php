<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Model;

use DateTimeInterface;

interface RefreshTokenInterface
{
    public function __toString(): string;

    public function getIdentifier(): string;

    public function getExpiry(): DateTimeInterface;

    public function getAccessToken(): ?AccessTokenInterface;

    public function isRevoked(): bool;

    public function revoke(): self;
}
