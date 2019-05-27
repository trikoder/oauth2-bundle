<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Manager;

use Trikoder\Bundle\OAuth2Bundle\Model\RefreshToken;

interface RefreshTokenManagerInterface
{
    public function find(string $identifier): ?RefreshToken;

    public function save(RefreshToken $refreshToken): void;

    public function clearExpired(): int;
}
