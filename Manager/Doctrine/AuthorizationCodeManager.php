<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Manager\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\AuthorizationCodeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\AuthorizationCode;

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
    public function find(string $identifier): ?AuthorizationCode
    {
        return $this->entityManager->find(AuthorizationCode::class, $identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function save(AuthorizationCode $authorizationCode): void
    {
        $this->entityManager->persist($authorizationCode);
        $this->entityManager->flush();
    }
}
