<?php

namespace Trikoder\Bundle\OAuth2Bundle\Manager\InMemory;

use Trikoder\Bundle\OAuth2Bundle\Manager\AuthCodeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\AuthCode;

final class AuthCodeManager implements AuthCodeManagerInterface
{
    /**
     * @var AuthCode[]
     */
    private $authCodes = [];

    public function find(string $identifier): ?AuthCode
    {
        return $this->authCodes[$identifier] ?? null;
    }

    public function save(AuthCode $authCode): void
    {
        $this->authCodes[$authCode->getIdentifier()] = $authCode;
    }
}
