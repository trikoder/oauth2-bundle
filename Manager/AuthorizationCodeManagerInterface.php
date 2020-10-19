<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Manager;

use Trikoder\Bundle\OAuth2Bundle\Model\AuthorizationCode;

/**
 * @method int clearRevoked() not defining this method is deprecated since version 3.2
 */
interface AuthorizationCodeManagerInterface
{
    public function find(string $identifier): ?AuthorizationCode;

    public function save(AuthorizationCode $authCode): void;

    public function clearExpired(): int;
}
