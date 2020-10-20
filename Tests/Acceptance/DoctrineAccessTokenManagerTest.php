<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\Doctrine\AccessTokenManager as DoctrineAccessTokenManager;
use Trikoder\Bundle\OAuth2Bundle\Model\AccessToken;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;
use Trikoder\Bundle\OAuth2Bundle\Model\RefreshToken;

/**
 * @TODO   This should be in the Integration tests folder but the current tests infrastructure would need improvements first.
 * @covers \Trikoder\Bundle\OAuth2Bundle\Manager\Doctrine\AccessTokenManager
 */
final class DoctrineAccessTokenManagerTest extends AbstractAcceptanceTest
{
    public function testClearExpired(): void
    {
        /** @var EntityManagerInterface $em */
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $doctrineAccessTokenManager = new DoctrineAccessTokenManager($em);

        $client = new Client('client', 'secret');
        $em->persist($client);
        $em->flush();

        timecop_freeze(new DateTimeImmutable());

        try {
            $testData = $this->buildClearExpiredTestData($client);

            /** @var AccessToken $token */
            foreach ($testData['input'] as $token) {
                $doctrineAccessTokenManager->save($token);
            }

            $this->assertSame(3, $doctrineAccessTokenManager->clearExpired());
        } finally {
            timecop_return();
        }

        $this->assertSame(
            $testData['output'],
            $em->getRepository(AccessToken::class)->findBy([], ['identifier' => 'ASC'])
        );
    }

    private function buildClearExpiredTestData(Client $client): array
    {
        $validAccessTokens = [
            $this->buildAccessToken($client, '1111', '+1 day'),
            $this->buildAccessToken($client, '2222', '+1 hour'),
            $this->buildAccessToken($client, '3333', '+1 second'),
            $this->buildAccessToken($client, '4444', 'now'),
        ];

        $expiredAccessTokens = [
            $this->buildAccessToken($client, '5555', '-1 day'),
            $this->buildAccessToken($client, '6666', '-1 hour'),
            $this->buildAccessToken($client, '7777', '-1 second'),
        ];

        return [
            'input' => array_merge($validAccessTokens, $expiredAccessTokens),
            'output' => $validAccessTokens,
        ];
    }

    public function testClearRevoked(): void
    {
        /** @var EntityManagerInterface $em */
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $doctrineAccessTokenManager = new DoctrineAccessTokenManager($em);

        $client = new Client('client', 'secret');
        $em->persist($client);
        $em->flush();

        $testData = $this->buildClearRevokedTestData($client);

        /** @var AccessToken $token */
        foreach ($testData['input'] as $token) {
            $doctrineAccessTokenManager->save($token);
        }

        $this->assertSame(2, $doctrineAccessTokenManager->clearRevoked());

        $this->assertSame(
            $testData['output'],
            $em->getRepository(AccessToken::class)->findBy([], ['identifier' => 'ASC'])
        );
    }

    private function buildClearRevokedTestData(Client $client): array
    {
        $validAccessTokens = [
            $this->buildAccessToken($client, '1111', '+1 day'),
            $this->buildAccessToken($client, '2222', '-1 hour'),
            $this->buildAccessToken($client, '3333', '+1 second'),
        ];

        $revokedAccessTokens = [
            $this->buildAccessToken($client, '5555', '-1 day', true),
            $this->buildAccessToken($client, '6666', '+1 hour', true),
        ];

        return [
            'input' => array_merge($validAccessTokens, $revokedAccessTokens),
            'output' => $validAccessTokens,
        ];
    }

    private function buildAccessToken(Client $client, string $identifier, string $modify, bool $revoked = false): AccessToken
    {
        $accessToken = new AccessToken(
            $identifier,
            new DateTimeImmutable($modify),
            $client,
            null,
            []
        );

        if ($revoked) {
            $accessToken->revoke();
        }

        return $accessToken;
    }

    public function testClearExpiredWithRefreshToken(): void
    {
        /** @var EntityManagerInterface $em */
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $doctrineAccessTokenManager = new DoctrineAccessTokenManager($em);

        $client = new Client('client', 'secret');
        $em->persist($client);
        $em->flush();

        timecop_freeze(new DateTimeImmutable());

        try {
            $testData = $this->buildClearExpiredTestDataWithRefreshToken($client);

            /** @var RefreshToken $token */
            foreach ($testData['input'] as $token) {
                $doctrineAccessTokenManager->save($token->getAccessToken());
                $em->persist($token);
            }

            $em->flush();

            $this->assertSame(3, $doctrineAccessTokenManager->clearExpired());
        } finally {
            timecop_return();
        }

        $mapFunction = function (RefreshToken $refreshToken): string {
            return $refreshToken->getIdentifier();
        };

        $this->assertSame(
            array_map($mapFunction, $testData['output']),
            array_map($mapFunction, $em->getRepository(RefreshToken::class)->findBy(['accessToken' => null], ['identifier' => 'ASC']))
        );
    }

    private function buildClearExpiredTestDataWithRefreshToken(Client $client): array
    {
        $validRefreshTokens = [
            $this->buildRefreshToken($client, '1111', '+1 day'),
            $this->buildRefreshToken($client, '2222', '+1 hour'),
            $this->buildRefreshToken($client, '3333', '+1 second'),
            $this->buildRefreshToken($client, '4444', 'now'),
        ];

        $expiredRefreshTokens = [
            $this->buildRefreshToken($client, '5555', '-1 day'),
            $this->buildRefreshToken($client, '6666', '-1 hour'),
            $this->buildRefreshToken($client, '7777', '-1 second'),
        ];

        return [
            'input' => array_merge($validRefreshTokens, $expiredRefreshTokens),
            'output' => $expiredRefreshTokens,
        ];
    }

    public function testClearRevokedWithRefreshToken(): void
    {
        /** @var EntityManagerInterface $em */
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $doctrineAccessTokenManager = new DoctrineAccessTokenManager($em);

        $client = new Client('client', 'secret');
        $em->persist($client);
        $em->flush();

        $testData = $this->buildClearRevokedTestDataWithRefreshToken($client);

        /** @var RefreshToken $token */
        foreach ($testData['input'] as $token) {
            $doctrineAccessTokenManager->save($token->getAccessToken());
            $em->persist($token);
        }

        $em->flush();

        $this->assertSame(2, $doctrineAccessTokenManager->clearRevoked());

        $savedData = $em->getRepository(RefreshToken::class)->findBy(['revoked' => true], ['identifier' => 'ASC']);

        $this->assertSame(
            $testData['output'],
            $savedData
        );
    }

    private function buildClearRevokedTestDataWithRefreshToken(Client $client): array
    {
        $validRefreshTokens = [
            $this->buildRefreshToken($client, '1111', '+1 day'),
            $this->buildRefreshToken($client, '2222', '+1 hour'),
            $this->buildRefreshToken($client, '3333', '+1 second'),
        ];

        $revokedRefreshTokens = [
            $this->buildRefreshToken($client, '5555', '-1 day', true),
            $this->buildRefreshToken($client, '6666', '-1 hour', true),
        ];

        return [
            'input' => array_merge($validRefreshTokens, $revokedRefreshTokens),
            'output' => $revokedRefreshTokens,
        ];
    }

    private function buildRefreshToken(Client $client, string $identifier, string $modify, bool $revoked = false): RefreshToken
    {
        $accessToken = new AccessToken(
            $identifier,
            new DateTimeImmutable($modify),
            $client,
            null,
            []
        );
        $refreshToken = new RefreshToken(
            $identifier,
            new DateTimeImmutable('+1 day'),
            $accessToken
        );

        if ($revoked) {
            $refreshToken->revoke();
            $accessToken->revoke();
        }

        return $refreshToken;
    }
}
