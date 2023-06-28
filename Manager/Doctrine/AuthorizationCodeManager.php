<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Manager\Doctrine;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\AuthorizationCodeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\AuthorizationCode;
use Trikoder\Bundle\OAuth2Bundle\Model\AuthorizationCodeInterface;

final class AuthorizationCodeManager implements AuthorizationCodeManagerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $identifier): ?AuthorizationCodeInterface
    {
        return $this->entityManager->find(AuthorizationCode::class, $identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function save(AuthorizationCodeInterface $authorizationCode): void
    {
        $this->entityManager->persist($authorizationCode);
        $this->entityManager->flush();
    }

    public function clearExpired(): int
    {
        return $this->entityManager->createQueryBuilder()
            ->delete(AuthorizationCode::class, 'ac')
            ->where('ac.expiry < :expiry')
            ->setParameter('expiry', new DateTimeImmutable())
            ->getQuery()
            ->execute();
    }

    public function clearRevoked(): int
    {
        return $this->entityManager->createQueryBuilder()
            ->delete(AuthorizationCode::class, 'ac')
            ->where('ac.revoked = true')
            ->getQuery()
            ->execute();
    }
}
