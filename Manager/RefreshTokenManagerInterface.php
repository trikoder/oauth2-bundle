<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Manager;

use Trikoder\Bundle\OAuth2Bundle\Model\RefreshTokenInterface;

/**
 * @method int clearRevoked() not defining this method is deprecated since version 3.2
 */
interface RefreshTokenManagerInterface
{
    public function find(string $identifier): ?RefreshTokenInterface;

    public function save(RefreshTokenInterface $refreshToken): void;

    public function clearExpired(): int;
}
