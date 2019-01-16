<?php

namespace Trikoder\Bundle\OAuth2Bundle\Manager\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\AuthCodeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\AuthCode;

final class AuthCodeManager implements AuthCodeManagerInterface
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
    public function find(string $identifier): ?AuthCode
    {
        return $this->entityManager->find(AuthCode::class, $identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function save(AuthCode $authCode): void
    {
        $this->entityManager->persist($authCode);
        $this->entityManager->flush();
    }
}
