<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\CryptoException;
use League\OAuth2\Server\CryptKey;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Trikoder\Bundle\OAuth2Bundle\League\Entity\AccessToken as AccessTokenEntity;
use Trikoder\Bundle\OAuth2Bundle\League\Entity\Client as ClientEntity;
use Trikoder\Bundle\OAuth2Bundle\League\Entity\Scope as ScopeEntity;
use Trikoder\Bundle\OAuth2Bundle\Model\AccessToken as AccessTokenModel;
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

    public static function generateJwtToken(AccessTokenModel $accessToken): string
    {
        $clientEntity = new ClientEntity();
        $clientEntity->setIdentifier($accessToken->getClient()->getIdentifier());
        $clientEntity->setRedirectUri(array_map('strval', $accessToken->getClient()->getRedirectUris()));

        $accessTokenEntity = new AccessTokenEntity();
        $accessTokenEntity->setIdentifier($accessToken->getIdentifier());
        $accessTokenEntity->setExpiryDateTime($accessToken->getExpiry());
        $accessTokenEntity->setClient($clientEntity);
        $accessTokenEntity->setUserIdentifier($accessToken->getUserIdentifier());

        foreach ($accessToken->getScopes() as $scope) {
            $scopeEntity = new ScopeEntity();
            $scopeEntity->setIdentifier((string) $scope);

            $accessTokenEntity->addScope($scopeEntity);
        }

        return (string) $accessTokenEntity->convertToJWT(
            new CryptKey(self::PRIVATE_KEY_PATH, null, false)
        );
    }

    public static function initializeDoctrineSchema(Application $application, array $arguments = []): bool
    {
        $statusCode = $application
            ->get('doctrine:schema:create')
            ->run(new ArrayInput($arguments), new NullOutput())
        ;

        return 0 === $statusCode;
    }
}
