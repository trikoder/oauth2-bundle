<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle;

final class OAuth2Events
{
    /**
     * The USER_RESOLVE event occurrs when the client requests a "password"
     * grant type from the authorization server.
     *
     * You should set a valid user here if applicable.
     */
    public const USER_RESOLVE = 'trikoder.oauth2.user_resolve';

    /**
     * The SCOPE_RESOLVE event occurrs right before the user obtains their
     * valid access token.
     *
     * You could alter the access token's scope here.
     */
    public const SCOPE_RESOLVE = 'trikoder.oauth2.scope_resolve';
}
