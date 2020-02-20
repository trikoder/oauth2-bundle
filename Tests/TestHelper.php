<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\CryptoException;
use Exception;
use League\OAuth2\Server\CryptKey;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Trikoder\Bundle\OAuth2Bundle\Converter\ScopeConverter;
use Trikoder\Bundle\OAuth2Bundle\League\Entity\AccessToken as AccessTokenEntity;
use Trikoder\Bundle\OAuth2Bundle\League\Entity\Client as ClientEntity;
use Trikoder\Bundle\OAuth2Bundle\League\Entity\Scope as ScopeEntity;
use Trikoder\Bundle\OAuth2Bundle\Model\AccessToken as AccessTokenModel;
use Trikoder\Bundle\OAuth2Bundle\Model\AuthorizationCode as AuthorizationCodeModel;
use Trikoder\Bundle\OAuth2Bundle\Model\RefreshToken as RefreshTokenModel;

final class TestHelper
{
    public const ENCRYPTION_KEY = '4kc8njQtPUazmzZGNt2Wh1rGO6bUFXatJQTnlKimX1Y=';
    public const PRIVATE_KEY_PATH = __DIR__ . '/Fixtures/private.key';
    public const PUBLIC_KEY_PATH = __DIR__ . '/Fixtures/public.key';

    public static function generateEncryptedPayload(RefreshTokenModel $refreshToken): ?string
    {
        $payload = json_encode([
            'client_id' => $refreshToken->getAccessToken()->getClient()->getIdentifier(),
            'refresh_token_id' => $refreshToken->getIdentifier(),
            'access_token_id' => $refreshToken->getAccessToken()->getIdentifier(),
            'scopes' => array_map('strval', $refreshToken->getAccessToken()->getScopes()),
            'user_id' => $refreshToken->getAccessToken()->getUserIdentifier(),
            'expire_time' => $refreshToken->getExpiry()->getTimestamp(),
        ]);

        try {
            return Crypto::encryptWithPassword($payload, self::ENCRYPTION_KEY);
        } catch (CryptoException $e) {
            return null;
        }
    }

    public static function generateEncryptedAuthCodePayload(AuthorizationCodeModel $authCode): ?string
    {
        $payload = json_encode([
            'client_id' => $authCode->getClient()->getIdentifier(),
            'redirect_uri' => (string) $authCode->getClient()->getRedirectUris()[0],
            'auth_code_id' => $authCode->getIdentifier(),
            'scopes' => (new ScopeConverter())->toDomainArray($authCode->getScopes()),
            'user_id' => $authCode->getUserIdentifier(),
            'expire_time' => $authCode->getExpiryDateTime()->getTimestamp(),
            'code_challenge' => null,
            'code_challenge_method' => null,
        ]);

        try {
            return Crypto::encryptWithPassword($payload, self::ENCRYPTION_KEY);
        } catch (CryptoException $e) {
            return null;
        }
    }

    public static function decryptPayload(string $payload): ?string
    {
        try {
            return Crypto::decryptWithPassword($payload, self::ENCRYPTION_KEY);
        } catch (CryptoException $e) {
            return null;
        }
    }

    public static function generateJwtToken(AccessTokenModel $accessToken): string
    {
        $clientEntity = new ClientEntity();
        $clientEntity->setIdentifier($accessToken->getClient()->getIdentifier());
        $clientEntity->setRedirectUri(array_map('strval', $accessToken->getClient()->getRedirectUris()));

        $accessTokenEntity = new AccessTokenEntity();
        $accessTokenEntity->setPrivateKey(new CryptKey(self::PRIVATE_KEY_PATH, null, false));
        $accessTokenEntity->setIdentifier($accessToken->getIdentifier());
        $accessTokenEntity->setExpiryDateTime($accessToken->getExpiry());
        $accessTokenEntity->setClient($clientEntity);
        $accessTokenEntity->setUserIdentifier($accessToken->getUserIdentifier());

        foreach ($accessToken->getScopes() as $scope) {
            $scopeEntity = new ScopeEntity();
            $scopeEntity->setIdentifier((string) $scope);

            $accessTokenEntity->addScope($scopeEntity);
        }

        return (string) $accessTokenEntity;
    }

    /**
     * @throws Exception
     */
    public static function initializeDoctrineSchema(Application $application, array $arguments = []): bool
    {
        $statusCode = $application
            ->get('doctrine:schema:create')
            ->run(new ArrayInput($arguments), new NullOutput())
        ;

        return 0 === $statusCode;
    }
}
