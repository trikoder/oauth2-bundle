<?php

namespace Trikoder\Bundle\OAuth2Bundle\Manager;

use Trikoder\Bundle\OAuth2Bundle\Model\AuthCode;

interface AuthCodeManagerInterface
{
    public function find(string $identifier): ?AuthCode;

    public function save(AuthCode $authCode): void;
}
