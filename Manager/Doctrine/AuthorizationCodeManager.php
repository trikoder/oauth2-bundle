<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Manager\Doctrine;

use Doctrine\Persistence\ObjectManager;
use Trikoder\Bundle\OAuth2Bundle\Manager\AuthorizationCodeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\AuthorizationCode;

final class AuthorizationCodeManager implements AuthorizationCodeManagerInterface
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $identifier): ?AuthorizationCode
    {
        return $this->objectManager->find(AuthorizationCode::class, $identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function save(AuthorizationCode $authorizationCode): void
    {
        $this->objectManager->persist($authorizationCode);
        $this->objectManager->flush();
    }
}
