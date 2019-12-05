<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle;

final class OAuth2Events
{
    /**
     * The USER_RESOLVE event occurs when the client requests a "password"
     * grant type from the authorization server.
     *
     * You should set a valid user here if applicable.
     */
    public const USER_RESOLVE = 'trikoder.oauth2.user_resolve';

    /**
     * The SCOPE_RESOLVE event occurs right before the user obtains their
     * valid access token.
     *
     * You could alter the access token's scope here.
     */
    public const SCOPE_RESOLVE = 'trikoder.oauth2.scope_resolve';

    /**
     * The AUTHORIZATION_REQUEST_RESOLVE event occurs right before the system
     * complete the authorization request.
     *
     * You could approve or deny the authorization request, or set the uri where
     * must be redirected to resolve the authorization request.
     */
    public const AUTHORIZATION_REQUEST_RESOLVE = 'trikoder.oauth2.authorization_request_resolve';

    /**
     * The AUTHORIZATION_CLAIMS_RESOLVE event occurs when the user requests
     * an id token from the OpenID Connect Provider
     *
     * You should set the user claims here if applicable.
     */
    public const AUTHORIZATION_CLAIMS_RESOLVE = 'trikoder.oauth2.claims_resolve';
}
