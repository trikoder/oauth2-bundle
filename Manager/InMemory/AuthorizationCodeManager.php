<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Manager\InMemory;

use DateTimeImmutable;
use Trikoder\Bundle\OAuth2Bundle\Manager\AuthorizationCodeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\AuthorizationCode;
use Trikoder\Bundle\OAuth2Bundle\Model\AuthorizationCodeInterface;

final class AuthorizationCodeManager implements AuthorizationCodeManagerInterface
{
    /**
     * @var AuthorizationCode[]
     */
    private $authorizationCodes = [];

    public function find(string $identifier): ?AuthorizationCodeInterface
    {
        return $this->authorizationCodes[$identifier] ?? null;
    }

    public function save(AuthorizationCodeInterface $authorizationCode): void
    {
        $this->authorizationCodes[$authorizationCode->getIdentifier()] = $authorizationCode;
    }

    public function clearExpired(): int
    {
        $count = \count($this->authorizationCodes);

        $now = new DateTimeImmutable();
        $this->authorizationCodes = array_filter($this->authorizationCodes, static function (AuthorizationCodeInterface $authorizationCode) use ($now): bool {
            return $authorizationCode->getExpiryDateTime() >= $now;
        });

        return $count - \count($this->authorizationCodes);
    }

    public function clearRevoked(): int
    {
        $count = \count($this->authorizationCodes);

        $this->authorizationCodes = array_filter($this->authorizationCodes, static function (AuthorizationCodeInterface $authorizationCode): bool {
            return !$authorizationCode->isRevoked();
        });

        return $count - \count($this->authorizationCodes);
    }
}
