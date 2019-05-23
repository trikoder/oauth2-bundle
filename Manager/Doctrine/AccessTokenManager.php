<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Manager\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\AccessTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\AccessToken;

final class AccessTokenManager extends TokenManager implements AccessTokenManagerInterface
{
    /**
     * AccessTokenManager constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);

        $this->class = AccessToken::class;
    }

    /**
     * @param string $identifier
     * @return AccessToken|null
     */
    public function find(string $identifier): ?AccessToken
    {
        return $this->entityManager->find(AccessToken::class, $identifier);
    }

    /**
     * @param AccessToken $accessToken
     */
    public function save(AccessToken $accessToken): void
    {
        $this->entityManager->persist($accessToken);
        $this->entityManager->flush();
    }
}
