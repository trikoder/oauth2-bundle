<?php

namespace Trikoder\Bundle\OAuth2Bundle\Event\Listener;

use Trikoder\Bundle\OAuth2Bundle\Event\AuthorizationRequestResolveEvent;

/**
 * Interface AuthorizationEventListener
 *
 * Event listeners implementing this interface can enrich authorization request
 * and generate response to interrupt authorization flow
 *
 * @package Trikoder\Bundle\OAuth2Bundle\Event\Listener
 */
interface AuthorizationEventListener
{
    /**
     * @param AuthorizationRequestResolveEvent $event
     */
    public function onAuthorizationRequest(AuthorizationRequestResolveEvent $event): void;
}
