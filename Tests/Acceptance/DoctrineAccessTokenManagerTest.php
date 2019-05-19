<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Unit;

use DateTime;
use Trikoder\Bundle\OAuth2Bundle\Manager\Doctrine\AccessTokenManager as DoctrineAccessTokenManager;
use Trikoder\Bundle\OAuth2Bundle\Model\AccessToken;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;
use Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance\AbstractAcceptanceTest;

/**
 * @TODO This should be in the Integration tests folder but the current tests infrastructure would need improvements first.
 */
final class DoctrineAccessTokenManagerTest extends AbstractAcceptanceTest
{
    public function testClearExpired(): void
    {
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $doctrineAccessTokenManager = new DoctrineAccessTokenManager($em);

        $client = new Client('client', 'secret');
        $em->persist($client);
        $em->flush();

        timecop_freeze(new DateTime());

        $testData = $this->buildClearExpiredTestData($client);

        /** @var AccessToken $token */
        foreach ($testData['input'] as $token) {
            $doctrineAccessTokenManager->save($token);
        }

        $this->assertSame(3, $doctrineAccessTokenManager->clearExpired());

        timecop_return();

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

    private function buildAccessToken(string $identifier, string $modify, Client $client): AccessToken
    {
        return new AccessToken(
            $identifier,
            (new DateTime())->modify($modify),
            $client,
            null,
            []
        );
    }
}
