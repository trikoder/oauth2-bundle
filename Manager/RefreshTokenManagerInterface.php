<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Manager;

use Trikoder\Bundle\OAuth2Bundle\Model\RefreshToken;

interface RefreshTokenManagerInterface extends TokenManagerInterface
{
    public function find(string $identifier): ?RefreshToken;

    /**
     * @param RefreshToken $refreshToken
     */
    public function save(/* RefreshToken */ $refreshToken): void;
}
