<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\Doctrine\RefreshTokenManager as DoctrineRefreshTokenManager;
use Trikoder\Bundle\OAuth2Bundle\Model\AccessToken;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;
use Trikoder\Bundle\OAuth2Bundle\Model\RefreshToken;

/**
 * @TODO   This should be in the Integration tests folder but the current tests infrastructure would need improvements first.
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

        timecop_freeze(new DateTimeImmutable());

        try {
            $testData = $this->buildClearExpiredTestData($client);

            /** @var RefreshToken $token */
            foreach ($testData['input'] as $token) {
                $em->persist($token->getAccessToken());
                $doctrineRefreshTokenManager->save($token);
            }

            $em->flush();

            $this->assertSame(3, $doctrineRefreshTokenManager->clearExpired());
        } finally {
            timecop_return();
        }

        $this->assertSame(
            $testData['output'],
            $em->getRepository(RefreshToken::class)->findBy([], ['identifier' => 'ASC'])
        );
    }

    private function buildClearExpiredTestData(Client $client): array
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
            $this->buildRefreshToken($client, '1111', '+1 day'),
            $this->buildRefreshToken($client, '2222', '-1 hour'),
            $this->buildRefreshToken($client, '3333', '+1 second'),
        ];

        $revokedRefreshTokens = [
            $this->buildRefreshToken($client, '5555', '-1 day', true),
            $this->buildRefreshToken($client, '6666', '+1 hour', true),
        ];

        return [
            'input' => array_merge($validRefreshTokens, $revokedRefreshTokens),
            'output' => $validRefreshTokens,
        ];
    }

    private function buildRefreshToken(Client $client, string $identifier, string $modify, bool $revoked = false): RefreshToken
    {
        $refreshToken = new RefreshToken(
            $identifier,
            new DateTimeImmutable($modify),
            new AccessToken(
                $identifier,
                new DateTimeImmutable('+1 day'),
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
