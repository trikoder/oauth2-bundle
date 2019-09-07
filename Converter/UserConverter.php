<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Converter;

use League\OAuth2\Server\Entities\UserEntityInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Trikoder\Bundle\OAuth2Bundle\League\Entity\User;

final class UserConverter implements UserConverterInterface
{
    public function toLeague(?UserInterface $user): UserEntityInterface
    {
        $userEntity = new User();
        if ($user instanceof UserInterface) {
            $userEntity->setIdentifier($user->getUsername());
        }

        return $userEntity;
    }
}
