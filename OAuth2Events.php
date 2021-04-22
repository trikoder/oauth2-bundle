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
     * The INVALID_CREDENTIALS event occurs when no user was found (invalid credentials)
     */
    public const INVALID_CREDENTIALS = 'trikoder.oauth2.invalid_credentials';

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
     * The AUTHORIZATION_HEADER_FAILURE event occurs when the
     * Authorization Bearer header was not found, or is wrong/malformed
     *
     * You can set a custom error message in the response body
     */
    public const AUTHORIZATION_HEADER_FAILURE = 'trikoder.oauth2.authorization_header_failure';

    /**
     * The AUTHENTICATION_FAILURE event occurs when the oauth token verification failed
     *
     * You can set a custom error message in the response body
     */
    public const AUTHENTICATION_FAILURE = 'trikoder.oauth2.authentication_failure';

    /**
     * The AUTHENTICATION_SCOPE_FAILURE event occurs when the scope validation for the token failed
     *
     * You can set a custom error message in the response body
     */
    public const AUTHENTICATION_SCOPE_FAILURE = 'trikoder.oauth2.authentication_scope_failure';

    /**
     * The AUTHORIZATION_SERVER_ERROR event occurs when the scope validation for the token failed
     *
     * You can set a custom error message in the response body
     */
    public const AUTHORIZATION_SERVER_ERROR = 'trikoder.oauth2.authorization_server_error';
}
