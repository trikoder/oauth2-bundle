<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Event\OauthEvent;

use Trikoder\Bundle\OAuth2Bundle\OAuth2Events;

/**
 * @author Benoit VIGNAL <github@benoit-vignal.fr>
 */
class InvalidCredentialsEvent extends AbstractOauthEvent
{
    function getEventName(): string
    {
        return OAuth2Events::INVALID_CREDENTIALS;
    }
}
