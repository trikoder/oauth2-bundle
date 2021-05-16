<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Event\OauthEvent;

use Trikoder\Bundle\OAuth2Bundle\OAuth2Events;

/**
 * @author Benoit VIGNAL <github@benoit-vignal.fr>
 */
class AuthorizationServerErrorEvent extends AbstractOauthEvent
{
    function getEventName(): string
    {
        return OAuth2Events::AUTHORIZATION_SERVER_ERROR;
    }
}
