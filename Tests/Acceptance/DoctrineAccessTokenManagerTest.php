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
            $this->buildAccessToken('1111', '+1 day', $client),
            $this->buildAccessToken('2222', '+1 hour', $client),
            $this->buildAccessToken('3333', '+1 second', $client),
            $this->buildAccessToken('4444', 'now', $client),
        ];

        $expiredAccessTokens = [
            $this->buildAccessToken('5555', '-1 day', $client),
            $this->buildAccessToken('6666', '-1 hour', $client),
            $this->buildAccessToken('7777', '-1 second', $client),
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
            $this->buildAccessToken('1111', '+1 day', $client),
            $this->buildAccessToken('2222', '-1 hour', $client),
            $this->buildAccessToken('3333', '+1 second', $client),
        ];

        $revokedAccessTokens = [
            $this->buildAccessToken('5555', '-1 day', $client, true),
            $this->buildAccessToken('6666', '+1 hour', $client, true),
        ];

        return [
            'input' => array_merge($validAccessTokens, $revokedAccessTokens),
            'output' => $validAccessTokens,
        ];
    }

    private function buildAccessToken(string $identifier, string $modify, Client $client, bool $revoked = false): AccessToken
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

        $this->assertSame(
            $testData['output'],
            $em->getRepository(RefreshToken::class)->findBy(['accessToken' => null], ['identifier' => 'ASC'])
        );
    }

    private function buildClearExpiredTestDataWithRefreshToken(Client $client): array
    {
        $validRefreshTokens = [
            $this->buildRefreshToken('1111', '+1 day', $client),
            $this->buildRefreshToken('2222', '+1 hour', $client),
            $this->buildRefreshToken('3333', '+1 second', $client),
            $this->buildRefreshToken('4444', 'now', $client),
        ];

        $expiredRefreshTokens = [
            $this->buildRefreshToken('5555', '-1 day', $client),
            $this->buildRefreshToken('6666', '-1 hour', $client),
            $this->buildRefreshToken('7777', '-1 second', $client),
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
            $this->buildRefreshToken('1111', '+1 day', $client),
            $this->buildRefreshToken('2222', '+1 hour', $client),
            $this->buildRefreshToken('3333', '+1 second', $client),
        ];

        $revokedRefreshTokens = [
            $this->buildRefreshToken('5555', '-1 day', $client, true),
            $this->buildRefreshToken('6666', '-1 hour', $client, true),
        ];

        return [
            'input' => array_merge($validRefreshTokens, $revokedRefreshTokens),
            'output' => $revokedRefreshTokens,
        ];
    }

    private function buildRefreshToken(string $identifier, string $modify, Client $client, bool $revoked = false): RefreshToken
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
