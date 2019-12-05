<?php

namespace Trikoder\Bundle\OAuth2Bundle\OpenIDConnect\Repository;

use OpenIDConnectServer\Repositories\IdentityProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trikoder\Bundle\OAuth2Bundle\Event\ClaimsResolveEvent;
use Trikoder\Bundle\OAuth2Bundle\OAuth2Events;
use Trikoder\Bundle\OAuth2Bundle\OpenIDConnect\Entity\Identity;

class IdentityProvider implements IdentityProviderInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getUserEntityByIdentifier($identifier)
    {
        $user = new Identity();
        $user->setIdentifier($identifier);

        $event = $this->eventDispatcher->dispatch(
            OAuth2Events::AUTHORIZATION_CLAIMS_RESOLVE,
            new ClaimsResolveEvent($identifier)
        );

        $user->setClaims($event->getClaims());

        return $user;
    }
}
