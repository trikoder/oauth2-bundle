<?php
namespace Trikoder\Bundle\OAuth2Bundle\Model\AuthorizationDecision;

use Trikoder\Bundle\OAuth2Bundle\Event\AuthorizationRequestResolveEvent;

interface AuthorizationDecisionStrategy
{
    public function decide(AuthorizationRequestResolveEvent $event);
}
