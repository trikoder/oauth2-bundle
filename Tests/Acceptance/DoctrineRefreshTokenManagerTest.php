<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Unit;

use DateTime;
use Trikoder\Bundle\OAuth2Bundle\Manager\Doctrine\RefreshTokenManager as DoctrineRefreshTokenManager;
use Trikoder\Bundle\OAuth2Bundle\Model\AccessToken;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;
use Trikoder\Bundle\OAuth2Bundle\Model\RefreshToken;
use Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance\AbstractAcceptanceTest;

/**
 * @TODO This should be in the Integration tests folder but the current tests infrastructure would need improvements first.
 */
final class DoctrineRefreshTokenManagerTest extends AbstractAcceptanceTest
{
    public function testClearExpired(): void
    {
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $doctrineRefreshTokenManager = new DoctrineRefreshTokenManager($em);

        $client = new Client('client', 'secret');
        $em->persist($client);
        $em->flush();

        timecop_freeze(new DateTime());

        $testData = $this->buildClearExpiredTestData($client);

        /** @var RefreshToken $token */
        foreach ($testData['input'] as $token) {
            $em->persist($token->getAccessToken());
            $doctrineRefreshTokenManager->save($token);
        }

        $this->assertSame(3, $doctrineRefreshTokenManager->clearExpired());

        timecop_return();

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
            'output' => $validRefreshTokens,
        ];
    }

    private function buildRefreshToken(string $identifier, string $modify, Client $client): RefreshToken
    {
        return new RefreshToken(
            $identifier,
            (new DateTime())->modify($modify),
            new AccessToken(
                $identifier,
                (new DateTime('+1 day')),
                $client,
                null,
                []
            )
        );
    }
}
