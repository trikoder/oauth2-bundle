<?php

namespace Trikoder\Bundle\OAuth2Bundle\EventListener;

use LogicException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Trikoder\Bundle\OAuth2Bundle\Event\AuthorizationRequestResolveEvent;

class CheckRequiredAuthorizationListener
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var mixed
     */
    private $requiredAttributes;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker, $requiredAttributes)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->requiredAttributes = $requiredAttributes;
    }

    public function onAuthorizationRequestResolveEvent(AuthorizationRequestResolveEvent $event): void
    {
        if (null === $this->requiredAttributes) {
            return;
        }

        if (!$this->authorizationChecker->isGranted($this->requiredAttributes)) {
            throw new LogicException(sprintf('The current authorization token does not grant required attributes "%s". Review your security configuration.', json_encode($this->requiredAttributes)));
        }
    }
}
