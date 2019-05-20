<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Fixtures;

use DateTime;
use Trikoder\Bundle\OAuth2Bundle\Manager\AccessTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\RefreshTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ScopeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\AccessToken;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;
use Trikoder\Bundle\OAuth2Bundle\Model\Grant;
use Trikoder\Bundle\OAuth2Bundle\Model\RefreshToken;
use Trikoder\Bundle\OAuth2Bundle\Model\Scope;

/**
 * Development hints:
 *
 * You can easily generate token identifiers using the following command:
 * --- dev/bin/php -r "echo bin2hex(random_bytes(40)) . PHP_EOL;"
 */
final class FixtureFactory
{
    public const FIXTURE_ACCESS_TOKEN_USER_BOUND = '96fb0ff864bf242425bfa7b9b6f47294fda556bf5eef78f753f61c2b827125d37d5d5735bcaed5b8';
    public const FIXTURE_ACCESS_TOKEN_DIFFERENT_CLIENT = '0481a78263176840b5895232e5f48737cee8a9e9ed950d89ca224a2e511027adf721248f7d974bf9';
    public const FIXTURE_ACCESS_TOKEN_EXPIRED = '7868b84c5369ffa36b5abf348681af65adb13294f1d5b27bd30be3003e8686c5cb354fd50554d6c1';
    public const FIXTURE_ACCESS_TOKEN_REVOKED = '3cf6fe59cd680bb064ac343fe1ca31affbd2f4239ee09fb2bb075016ee1fe018097dbb41bbfee447';
    public const FIXTURE_ACCESS_TOKEN_PUBLIC = '237e43f38e0ee9153bbca884bdf1449cdb881001831265b1e898560697a87dc65b95f2181e643d37';
    public const FIXTURE_ACCESS_TOKEN_WITH_SCOPES = 'e56bb47d864633b2212650474df518ca7636b69ea15d8d6a143388da1889d88d8fef9082ac16074c';
    public const FIXTURE_ACCESS_TOKEN_USER_BOUND_WITH_SCOPES = '4ba73a8fff5de0920b1b07f4f1f0f000d8521dd2c9469a31341ea8a310adc268bcc951f53c970f0e';

    public const FIXTURE_REFRESH_TOKEN = '251878ac72f428961edb1df98868b8af3d988bc94c0b589d5aeb7eb0ac32c1da61db9a547b5ce4ad';
    public const FIXTURE_REFRESH_TOKEN_DIFFERENT_CLIENT = '73b1618470fdccf1c96eda132f8a19d6da43c31e2efd19daeab2c98c0ac36bf95b3ea72fdc8d6752';
    public const FIXTURE_REFRESH_TOKEN_EXPIRED = '3b3db453a137debb7b5f445c971bef18bb4f045d272a66a27054a0713096d2a8377679d204495c88';
    public const FIXTURE_REFRESH_TOKEN_REVOKED = '63641841630c2e4d747e0f9ebe12ee04424e322874b8e68ef69fd58f1899ef70beb09733e23928a6';
    public const FIXTURE_REFRESH_TOKEN_WITH_SCOPES = 'e47d593ed661840b3633e4577c3261ef57ba225be193b190deb69ee9afefdc19f54f890fbdda59f5';

    public const FIXTURE_CLIENT_FIRST = 'foo';
    public const FIXTURE_CLIENT_SECOND = 'bar';
    public const FIXTURE_CLIENT_INACTIVE = 'baz_inactive';
    public const FIXTURE_CLIENT_RESTRICTED_GRANTS = 'qux_restricted_grants';
    public const FIXTURE_CLIENT_RESTRICTED_SCOPES = 'quux_restricted_scopes';

    public const FIXTURE_SCOPE_FIRST = 'fancy';
    public const FIXTURE_SCOPE_SECOND = 'rock';

    public const FIXTURE_USER = 'user';

    public static function createUser(array $roles = []): User
    {
        $user = new User();
        $user['roles'] = $roles;

        return $user;
    }

    public static function initializeFixtures(
        ScopeManagerInterface $scopeManager,
        ClientManagerInterface $clientManager,
        AccessTokenManagerInterface $accessTokenManager,
        RefreshTokenManagerInterface $refreshTokenManager
    ): void {
        foreach (self::createScopes() as $scope) {
            $scopeManager->save($scope);
        }

        foreach (self::createClients() as $client) {
            $clientManager->save($client);
        }

        foreach (self::createAccessTokens($scopeManager, $clientManager) as $accessToken) {
            $accessTokenManager->save($accessToken);
        }

        foreach (self::createRefreshTokens($accessTokenManager) as $refreshToken) {
            $refreshTokenManager->save($refreshToken);
        }
    }

    /**
     * @return AccessToken[]
     */
    private static function createAccessTokens(ScopeManagerInterface $scopeManager, ClientManagerInterface $clientManager): array
    {
        $accessTokens = [];

        $accessTokens[] = (new AccessToken(
            self::FIXTURE_ACCESS_TOKEN_USER_BOUND,
            new DateTime('+1 hour'),
            $clientManager->find(self::FIXTURE_CLIENT_FIRST),
            self::FIXTURE_USER,
            []
        ));

        $accessTokens[] = (new AccessToken(
            self::FIXTURE_ACCESS_TOKEN_DIFFERENT_CLIENT,
            new DateTime('+1 hour'),
            $clientManager->find(self::FIXTURE_CLIENT_SECOND),
            self::FIXTURE_USER,
            []
        ));

        $accessTokens[] = (new AccessToken(
            self::FIXTURE_ACCESS_TOKEN_EXPIRED,
            new DateTime('-1 hour'),
            $clientManager->find(self::FIXTURE_CLIENT_FIRST),
            self::FIXTURE_USER,
            []
        ));

        $accessTokens[] = (new AccessToken(
            self::FIXTURE_ACCESS_TOKEN_REVOKED,
            new DateTime('+1 hour'),
            $clientManager->find(self::FIXTURE_CLIENT_FIRST),
            self::FIXTURE_USER,
            []
        ))
            ->revoke();

        $accessTokens[] = new AccessToken(
            self::FIXTURE_ACCESS_TOKEN_PUBLIC,
            new DateTime('+1 hour'),
            $clientManager->find(self::FIXTURE_CLIENT_FIRST),
            null,
            []
        );

        $accessTokens[] = (new AccessToken(
            self::FIXTURE_ACCESS_TOKEN_WITH_SCOPES,
            new DateTime('+1 hour'),
            $clientManager->find(self::FIXTURE_CLIENT_FIRST),
            null,
            [$scopeManager->find(self::FIXTURE_SCOPE_FIRST)]
        ));

        $accessTokens[] = (new AccessToken(
            self::FIXTURE_ACCESS_TOKEN_USER_BOUND_WITH_SCOPES,
            new DateTime('+1 hour'),
            $clientManager->find(self::FIXTURE_CLIENT_FIRST),
            self::FIXTURE_USER,
            [$scopeManager->find(self::FIXTURE_SCOPE_FIRST)]
        ));

        return $accessTokens;
    }

    /**
     * @return RefreshToken[]
     */
    private static function createRefreshTokens(AccessTokenManagerInterface $accessTokenManager): array
    {
        $refreshTokens = [];

        $refreshTokens[] = new RefreshToken(
            self::FIXTURE_REFRESH_TOKEN,
            new DateTime('+1 month'),
            $accessTokenManager->find(self::FIXTURE_ACCESS_TOKEN_USER_BOUND)
        );

        $refreshTokens[] = new RefreshToken(
            self::FIXTURE_REFRESH_TOKEN_DIFFERENT_CLIENT,
            new DateTime('+1 month'),
            $accessTokenManager->find(self::FIXTURE_ACCESS_TOKEN_DIFFERENT_CLIENT)
        );

        $refreshTokens[] = new RefreshToken(
            self::FIXTURE_REFRESH_TOKEN_EXPIRED,
            new DateTime('-1 month'),
            $accessTokenManager->find(self::FIXTURE_ACCESS_TOKEN_EXPIRED)
        );

        $refreshTokens[] = (new RefreshToken(
            self::FIXTURE_REFRESH_TOKEN_REVOKED,
            new DateTime('+1 month'),
            $accessTokenManager->find(self::FIXTURE_ACCESS_TOKEN_REVOKED)
        ))
            ->revoke();

        $refreshTokens[] = new RefreshToken(
            self::FIXTURE_REFRESH_TOKEN_WITH_SCOPES,
            new DateTime('+1 month'),
            $accessTokenManager->find(self::FIXTURE_ACCESS_TOKEN_USER_BOUND_WITH_SCOPES)
        );

        return $refreshTokens;
    }

    /**
     * @return Client[]
     */
    private static function createClients(): array
    {
        $clients = [];

        $clients[] = new Client(self::FIXTURE_CLIENT_FIRST, 'secret');

        $clients[] = new Client(self::FIXTURE_CLIENT_SECOND, 'top_secret');

        $clients[] = (new Client(self::FIXTURE_CLIENT_INACTIVE, 'woah'))
            ->setActive(false);

        $clients[] = (new Client(self::FIXTURE_CLIENT_RESTRICTED_GRANTS, 'wicked'))
            ->setGrants(new Grant('password'));

        $clients[] = (new Client(self::FIXTURE_CLIENT_RESTRICTED_SCOPES, 'beer'))
            ->setScopes(new Scope(self::FIXTURE_SCOPE_SECOND));

        return $clients;
    }

    /**
     * @return Scope[]
     */
    private static function createScopes(): array
    {
        $scopes = [];

        $scopes[] = new Scope(self::FIXTURE_SCOPE_FIRST);
        $scopes[] = new Scope(self::FIXTURE_SCOPE_SECOND);

        return $scopes;
    }
}
