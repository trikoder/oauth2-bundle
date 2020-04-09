<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Service\CredentialsRevoker;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\AccessToken;
use Trikoder\Bundle\OAuth2Bundle\Model\AuthorizationCode;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;
use Trikoder\Bundle\OAuth2Bundle\Model\RefreshToken;
use Trikoder\Bundle\OAuth2Bundle\Service\CredentialsRevokerInterface;

final class DoctrineCredentialsRevoker implements CredentialsRevokerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function revokeCredentialsForUser(UserInterface $user): void
    {
        $userIdentifier = $user->getUsername();

        $this->entityManager->createQueryBuilder()
            ->update(AccessToken::class, 'at')
            ->set('at.revoked', true)
            ->where('at.userIdentifier = :userIdentifier')
            ->setParameter('userIdentifier', $userIdentifier)
            ->getQuery()
            ->execute();

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->update(RefreshToken::class, 'rt')
            ->set('rt.revoked', true)
            ->where($queryBuilder->expr()->in(
                'rt.accessToken',
                $this->entityManager->createQueryBuilder()
                    ->select('at.identifier')
                    ->from(AccessToken::class, 'at')
                    ->where('at.userIdentifier = :userIdentifier')
                    ->getDQL()
            ))
            ->setParameter('userIdentifier', $userIdentifier)
            ->getQuery()
            ->execute();

        $this->entityManager->createQueryBuilder()
            ->update(AuthorizationCode::class, 'ac')
            ->set('ac.revoked', true)
            ->where('ac.userIdentifier = :userIdentifier')
            ->setParameter('userIdentifier', $userIdentifier)
            ->getQuery()
            ->execute();
    }

    public function revokeCredentialsForClient(Client $client): void
    {
        $doctrineClient = $this->entityManager
            ->getRepository(Client::class)
            ->findOneBy(['identifier' => $client->getIdentifier()]);

        $this->entityManager->createQueryBuilder()
            ->update(AccessToken::class, 'at')
            ->set('at.revoked', true)
            ->where('at.client = :client')
            ->setParameter('client', $doctrineClient)
            ->getQuery()
            ->execute();

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->update(RefreshToken::class, 'rt')
            ->set('rt.revoked', true)
            ->where($queryBuilder->expr()->in(
                'rt.accessToken',
                $this->entityManager->createQueryBuilder()
                    ->select('at.identifier')
                    ->from(AccessToken::class, 'at')
                    ->where('at.client = :client')
                    ->getDQL()
            ))
            ->setParameter('client', $doctrineClient)
            ->getQuery()
            ->execute();

        $this->entityManager->createQueryBuilder()
            ->update(AuthorizationCode::class, 'ac')
            ->set('ac.revoked', true)
            ->where('ac.client = :client')
            ->setParameter('client', $doctrineClient)
            ->getQuery()
            ->execute();
    }
}
