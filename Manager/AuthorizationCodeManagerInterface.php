<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Manager;

use Trikoder\Bundle\OAuth2Bundle\Model\AuthorizationCode;

interface AuthorizationCodeManagerInterface
{
    public function find(string $identifier): ?AuthorizationCode;

    public function save(AuthorizationCode $authCode): void;
}
