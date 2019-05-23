<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Manager\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\RefreshTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\RefreshToken;

final class RefreshTokenManager extends TokenManager implements RefreshTokenManagerInterface
{
    /**
     * RefreshTokenManager constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);

        $this->class = RefreshToken::class;
    }

    /**
     * @param string $identifier
     * @return RefreshToken|null
     */
    public function find(string $identifier): ?RefreshToken
    {
        return $this->entityManager->find(RefreshToken::class, $identifier);
    }

    /**
     * @param RefreshToken $refreshToken
     */
    public function save(RefreshToken $refreshToken): void
    {
        $this->entityManager->persist($refreshToken);
        $this->entityManager->flush();
    }
}
