<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
final class NullUser implements UserInterface
{
    public function getUsername(): string
    {
        return '';
    }

    public function getPassword(): string
    {
        return '';
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getRoles(): array
    {
        return [];
    }

    public function eraseCredentials(): void
    {
        return;
    }
}
