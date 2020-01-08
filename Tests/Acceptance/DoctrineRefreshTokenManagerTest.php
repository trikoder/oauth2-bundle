<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use DateTime;
use Doctrine\Persistence\ObjectManager;
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
    public function testClearExpiredORM(): void
    {
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->clearExpired($em);
    }

    public function testClearExpiredODM(): void
    {
        $dm = $this->client->getContainer()->get('doctrine_mongodb.odm.document_manager');
        $this->clearExpired($dm);
    }

    private function clearExpired(ObjectManager $objectManager)
    {
        $doctrineRefreshTokenManager = new DoctrineRefreshTokenManager($objectManager);

        $client = new Client('client', 'secret');
        $objectManager->persist($client);
        $objectManager->flush();

        timecop_freeze(new DateTime());

        try {
            $testData = $this->buildClearExpiredTestData($client);

            /** @var RefreshToken $token */
            foreach ($testData['input'] as $token) {
                $objectManager->persist($token->getAccessToken());
                $doctrineRefreshTokenManager->save($token);
            }

            $objectManager->flush();

            $this->assertSame(3, $doctrineRefreshTokenManager->clearExpired());
        } finally {
            timecop_return();
        }

        $this->assertSame(
            $testData['output'],
            $objectManager->getRepository(RefreshToken::class)->findBy([], ['identifier' => 'ASC'])
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
