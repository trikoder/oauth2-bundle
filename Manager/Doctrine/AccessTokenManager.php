<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Manager\Doctrine;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\AccessTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\AccessToken;

final class AccessTokenManager implements AccessTokenManagerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /** @var bool */
    private $disableAccessTokenSaving;

    public function __construct(EntityManagerInterface $entityManager, bool $disableAccessTokenSaving)
    {
        $this->entityManager = $entityManager;
        $this->disableAccessTokenSaving = $disableAccessTokenSaving;
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $identifier): ?AccessToken
    {
        if ($this->disableAccessTokenSaving) {
            return null;
        }

        return $this->entityManager->find(AccessToken::class, $identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function save(AccessToken $accessToken): void
    {
        if ($this->disableAccessTokenSaving) {
            return;
        }

        $this->entityManager->persist($accessToken);
        $this->entityManager->flush();
    }

    public function clearExpired(): int
    {
        if ($this->disableAccessTokenSaving) {
            return 0;
        }

        return $this->entityManager->createQueryBuilder()
            ->delete(AccessToken::class, 'at')
            ->where('at.expiry < :expiry')
            ->setParameter('expiry', new DateTimeImmutable())
            ->getQuery()
            ->execute();
    }
}
