<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Manager\InMemory;

use Trikoder\Bundle\OAuth2Bundle\Manager\AuthorizationCodeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\AuthorizationCode;

final class AuthorizationCodeManager implements AuthorizationCodeManagerInterface
{
    /**
     * @var AuthorizationCode[]
     */
    private $authorizationCodes = [];

    public function find(string $identifier): ?AuthorizationCode
    {
        return $this->authorizationCodes[$identifier] ?? null;
    }

    public function save(AuthorizationCode $authorizationCode): void
    {
        $this->authorizationCodes[$authorizationCode->getIdentifier()] = $authorizationCode;
    }
}
