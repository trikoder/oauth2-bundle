<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use Doctrine\ORM\EntityManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\Doctrine\RefreshTokenManager as DoctrineRefreshTokenManager;
use Trikoder\Bundle\OAuth2Bundle\Model\AccessToken;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;
use Trikoder\Bundle\OAuth2Bundle\Model\RefreshToken;

/**
 * @TODO   This should be in the Integration tests folder but the current tests infrastructure would need improvements first.
 *
 * @covers \Trikoder\Bundle\OAuth2Bundle\Manager\Doctrine\RefreshTokenManager
 */
final class DoctrineRefreshTokenManagerTest extends AbstractAcceptanceTest
{
    public function testClearExpired(): void
    {
        /** @var EntityManagerInterface $em */
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $doctrineRefreshTokenManager = new DoctrineRefreshTokenManager($em);

        $client = new Client('client', 'secret');
        $em->persist($client);
        $em->flush();

        $testData = $this->buildClearExpiredTestData($client);

        /** @var RefreshToken $token */
        foreach ($testData['input'] as $token) {
            $em->persist($token->getAccessToken());
            $doctrineRefreshTokenManager->save($token);
        }

        $em->flush();

        $this->assertSame(3, $doctrineRefreshTokenManager->clearExpired());

        $this->assertSame(
            $testData['output'],
            $em->getRepository(RefreshToken::class)->findBy([], ['identifier' => 'ASC'])
        );
    }

    private function buildClearExpiredTestData(Client $client): array
    {
        $validRefreshTokens = [
            $this->buildRefreshToken('1111', '+1 day', $client),
            $this->buildRefreshToken('2222', '+1 hour', $client),
            $this->buildRefreshToken('3333', '+5 second', $client),
        ];

        $expiredRefreshTokens = [
            $this->buildRefreshToken('5555', '-1 day', $client),
            $this->buildRefreshToken('6666', '-1 hour', $client),
            $this->buildRefreshToken('7777', '-1 second', $client),
        ];

        return [
            'input' => array_merge($validRefreshTokens, $expiredRefreshTokens),
            'output' => $validRefreshTokens,
        ];
    }

    public function testClearRevoked(): void
    {
        /** @var EntityManagerInterface $em */
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $doctrineRefreshTokenManager = new DoctrineRefreshTokenManager($em);

        $client = new Client('client', 'secret');
        $em->persist($client);
        $em->flush();

        $testData = $this->buildClearRevokedTestData($client);

        /** @var RefreshToken $token */
        foreach ($testData['input'] as $token) {
            $em->persist($token->getAccessToken());
            $doctrineRefreshTokenManager->save($token);
        }

        $em->flush();

        $this->assertSame(2, $doctrineRefreshTokenManager->clearRevoked());

        $this->assertSame(
            $testData['output'],
            $em->getRepository(RefreshToken::class)->findBy([], ['identifier' => 'ASC'])
        );
    }

    private function buildClearRevokedTestData(Client $client): array
    {
        $validRefreshTokens = [
            $this->buildRefreshToken('1111', '+1 day', $client),
            $this->buildRefreshToken('2222', '-1 hour', $client),
            $this->buildRefreshToken('3333', '+5 second', $client),
        ];

        $revokedRefreshTokens = [
            $this->buildRefreshToken('5555', '-1 day', $client, true),
            $this->buildRefreshToken('6666', '+1 hour', $client, true),
        ];

        return [
            'input' => array_merge($validRefreshTokens, $revokedRefreshTokens),
            'output' => $validRefreshTokens,
        ];
    }

    private function buildRefreshToken(string $identifier, string $modify, Client $client, bool $revoked = false): RefreshToken
    {
        $refreshToken = new RefreshToken(
            $identifier,
            new \DateTimeImmutable($modify),
            new AccessToken(
                $identifier,
                new \DateTimeImmutable('+1 day'),
                $client,
                null,
                []
            )
        );

        if ($revoked) {
            $refreshToken->revoke();
        }

        return $refreshToken;
    }
}
