<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Fixtures;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\User\UserInterface;

final class SecurityTestController extends AbstractController
{
    public function helloAction(): Response
    {
        /** @var UserInterface $user */
        $user = $this->getUser();

        return new Response(
            sprintf('Hello, %s', (null !== $user) ? $user->getUsername() : 'guest')
        );
    }

    public function scopeAction(): Response
    {
        return new Response('Only certain scopes should be able to access this action.');
    }

    public function rolesAction(): Response
    {
        /** @var TokenStorage $tokenStorage */
        $tokenStorage = $this->get('security.token_storage');
        $roles = $tokenStorage->getToken()->getRoleNames();

        return new Response(
            sprintf(
                'These are the roles I have currently assigned: %s',
                implode(', ', $roles)
            )
        );
    }
}
