<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Fixtures;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class SecurityTestController extends AbstractController
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

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
        $roles = $this->tokenStorage->getToken()->getRoleNames();

        return new Response(
            sprintf(
                'These are the roles I have currently assigned: %s',
                implode(', ', $roles)
            )
        );
    }
}
