<?php

namespace Trikoder\Bundle\OAuth2Bundle\Event\Listener;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Trikoder\Bundle\OAuth2Bundle\Event\AuthorizationRequestResolveEvent;
use Trikoder\Bundle\OAuth2Bundle\League\Entity\User;

/**
 * Class AuthorizationRequestUserResolvingListener
 *
 * Listener sets currently authenticated user to authorization request context
 *
 * @package Trikoder\Bundle\OAuth2Bundle\Event\Listener
 */
class AuthorizationRequestUserResolvingListener
{
    /**
     * @var Security
     */
    private $security;

    /**
     * AuthorizationRequestUserResolvingListener constructor.
     *
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function onAuthorizationRequest(AuthorizationRequestResolveEvent $authRequest)
    {
        $authRequest->setUser($this->getUserEntity());
    }

    private function getUserEntity(): User
    {
        $userEntity = new User();

        $user = $this->security->getUser();
        if ($user) {
            $username = $user instanceof UserInterface ? $user->getUsername() : (string) $user;
            $userEntity->setIdentifier($username);
        }

        return $userEntity;
    }
}
