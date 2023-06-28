<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Manager;

use Trikoder\Bundle\OAuth2Bundle\Model\AccessTokenInterface;

/**
 * @method int clearRevoked() not defining this method is deprecated since version 3.2
 */
interface AccessTokenManagerInterface
{
    public function find(string $identifier): ?AccessTokenInterface;

    public function save(AccessTokenInterface $accessToken): void;

    public function clearExpired(): int;
}
