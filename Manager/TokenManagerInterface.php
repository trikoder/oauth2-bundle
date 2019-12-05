<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Manager;

use Trikoder\Bundle\OAuth2Bundle\Model\TokenInterface;

interface TokenManagerInterface
{
    public function find(string $identifier);

    public function save(TokenInterface $refreshToken): void;

    public function clearExpired(): int;
}
