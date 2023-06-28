<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Model;

use DateTimeInterface;

interface AccessTokenInterface
{
    public function __toString(): string;

    public function getIdentifier(): string;

    public function getExpiry(): DateTimeInterface;

    public function getUserIdentifier(): ?string;

    public function getClient(): ClientInterface;

    public function getScopes(): array;

    public function isRevoked(): bool;

    public function revoke(): self;
}
