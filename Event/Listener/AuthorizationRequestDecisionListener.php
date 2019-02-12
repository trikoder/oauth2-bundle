<?php

namespace Trikoder\Bundle\OAuth2Bundle\Event\Listener;

use Trikoder\Bundle\OAuth2Bundle\Event\AuthorizationRequestResolveEvent;
use Trikoder\Bundle\OAuth2Bundle\Model\AuthorizationDecision\AuthorizationDecisionStrategy;

/**
 * Class AuthorizationRequestDecisionListener
 *
 * Listener delegates to configured authorization strategy (redirect to consent screen, auto-approve)
 *
 * @package Trikoder\Bundle\OAuth2Bundle\Event\Listener
 */
class AuthorizationRequestDecisionListener implements AuthorizationEventListener
{
    /**
     * @var AuthorizationDecisionStrategy
     */
    private $authorizationDecisionStrategy;

    /**
     * AuthorizationRequestDecisionListener constructor.
     * @param AuthorizationDecisionStrategy $authorizationDecisionStrategy
     */
    public function __construct(AuthorizationDecisionStrategy $authorizationDecisionStrategy)
    {
        $this->authorizationDecisionStrategy = $authorizationDecisionStrategy;
    }

    public function onAuthorizationRequest(AuthorizationRequestResolveEvent $event): void
    {
        // if request is already approved by other listener, there is nothing left to do
        if ($event->isAuthorizationApproved()) {
            return;
        }

        // delegate to configured authorization strategy
        $this->authorizationDecisionStrategy->decide($event);
    }
}
