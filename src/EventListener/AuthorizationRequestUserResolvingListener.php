<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\EventListener;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Trikoder\Bundle\OAuth2Bundle\Event\AuthorizationRequestResolveEvent;

/**
 * Listener sets currently authenticated user to authorization request context
 */
final class AuthorizationRequestUserResolvingListener
{
    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function onAuthorizationRequest(AuthorizationRequestResolveEvent $event): void
    {
        $user = $this->security->getUser();
        if ($user instanceof UserInterface) {
            $event->setUser($user);
        }
    }
}
