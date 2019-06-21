<?php

namespace Trikoder\Bundle\OAuth2Bundle\Model\AuthorizationDecision;

use Trikoder\Bundle\OAuth2Bundle\Event\AuthorizationRequestResolveEvent;

class AlwaysAllowDecisionStrategy implements AuthorizationDecisionStrategy
{
    public function decide(AuthorizationRequestResolveEvent $event)
    {
        $event->resolveAuthorization(true);
    }
}
