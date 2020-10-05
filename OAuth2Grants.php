<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle;

final class OAuth2Grants
{
    /**
     * @see https://tools.ietf.org/html/rfc6749#section-1.3.1
     *
     * @var string
     */
    public const AUTHORIZATION_CODE = 'authorization_code';

    /**
     * @see https://tools.ietf.org/html/rfc6749#section-1.3.4
     *
     * @var string
     */
    public const CLIENT_CREDENTIALS = 'client_credentials';

    /**
     * @see https://tools.ietf.org/html/rfc6749#section-1.3.2
     *
     * @var string
     */
    public const IMPLICIT = 'implicit';

    /**
     * @see https://tools.ietf.org/html/rfc6749#section-1.3.3
     *
     * @var string
     */
    public const PASSWORD = 'password';

    /**
     * @see https://tools.ietf.org/html/rfc6749#section-6
     *
     * @var string
     */
    public const REFRESH_TOKEN = 'refresh_token';

    public const WITH_REFRESH_TOKEN = [
        'authorization_code',
        'password',
        'refresh_token',
    ];

    public const ALL = [
        self::AUTHORIZATION_CODE => 'authorization code',
        self::CLIENT_CREDENTIALS => 'client credentials',
        self::IMPLICIT => 'implicit',
        self::PASSWORD => 'password',
        self::REFRESH_TOKEN => 'refresh token',
    ];

    /**
     * @deprecated Will be removed in v4, use {@see OAuth2Grants::ALL} instead
     *
     * @TODO Remove in v4.
     */
    public static function has(string $grant): bool
    {
        return isset(self::ALL[$grant]);
    }
}
