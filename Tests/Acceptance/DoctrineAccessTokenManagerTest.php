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
            $testData = $this->buildTestData(
                $client,
                function (array $item): bool {
                    return !$item['expired'];
                }
            );

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

    public function testClearRevoked(): void
    {
        /** @var EntityManagerInterface $em */
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $doctrineAccessTokenManager = new DoctrineAccessTokenManager($em);

        $client = new Client('client', 'secret');
        $em->persist($client);
        $em->flush();

        timecop_freeze(new DateTimeImmutable());

        try {
            $testData = $this->buildTestData(
                $client,
                function (array $item): bool {
                    return !$item['revoked'];
                }
            );

            /** @var AccessToken $token */
            foreach ($testData['input'] as $token) {
                $doctrineAccessTokenManager->save($token);
            }

            $this->assertSame(4, $doctrineAccessTokenManager->clearRevoked());
        } finally {
            timecop_return();
        }

        $this->assertSame(
            $testData['output'],
            $em->getRepository(AccessToken::class)->findBy([], ['identifier' => 'ASC'])
        );
    }

    private function getData(): array
    {
        return [
            [
                'identifier' => '1111',
                'dateOffset' => '+1 day',
                'revoked' => true,
                'expired' => false,
            ],
            [
                'identifier' => '2222',
                'dateOffset' => '+1 hour',
                'revoked' => false,
                'expired' => false,
            ],
            [
                'identifier' => '3333',
                'dateOffset' => '+1 second',
                'revoked' => true,
                'expired' => false,
            ],
            [
                'identifier' => '4444',
                'dateOffset' => 'now',
                'revoked' => false,
                'expired' => false,
            ],
            [
                'identifier' => '5555',
                'dateOffset' => '-1 day',
                'revoked' => true,
                'expired' => true,
            ],
            [
                'identifier' => '6666',
                'dateOffset' => '-1 hour',
                'revoked' => false,
                'expired' => true,
            ],
            [
                'identifier' => '7777',
                'dateOffset' => '-1 second',
                'revoked' => true,
                'expired' => true,
            ]
        ];
    }

    private function buildTestData(Client $client, callable $successFunction): array
    {
        $response = [];
        foreach ($this->getData() as $item) {
            $identifier = $item['identifier'];
            $accessToken = $this->buildAccessToken(
                $client,
                $identifier,
                $item['dateOffset'],
                $item['revoked']
            );
            $response['input'][] = $accessToken;

            if ($successFunction($item)) {
                $response['output'][] = $accessToken;
            }
        }

        return $response;
    }

    private function buildTestDataWithRefreshToken(Client $client, callable $successFunction): array
    {
        $response = [];
        foreach ($this->getData() as $item) {
            $identifier = $item['identifier'];
            $accessToken = $this->buildRefreshToken(
                $client,
                $identifier,
                $item['dateOffset'],
                $item['revoked']
            );
            $response['input'][] = $accessToken;

            if ($successFunction($item)) {
                $response['output'][] = $accessToken;
            }
        }

        return $response;
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
            $testData = $this->buildTestDataWithRefreshToken(
                $client,
                function (array $item): bool {
                    return $item['expired'];
                }
            );

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

        $em->clear();

        $mapFunction = function (RefreshToken $refreshToken): string {
            return $refreshToken->getIdentifier();
        };

        $this->assertSame(
            array_map($mapFunction, $testData['output']),
            array_map($mapFunction, $em->getRepository(RefreshToken::class)->findBy(['accessToken' => null], ['identifier' => 'ASC']))
        );
    }

    public function testClearRevokedWithRefreshToken(): void
    {
        /** @var EntityManagerInterface $em */
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $doctrineAccessTokenManager = new DoctrineAccessTokenManager($em);

        $client = new Client('client', 'secret');
        $em->persist($client);
        $em->flush();

        timecop_freeze(new DateTimeImmutable());

        try {
            $testData = $this->buildTestDataWithRefreshToken(
                $client,
                function (array $item): bool {
                    return !$item['revoked'];
                }
            );

            /** @var RefreshToken $token */
            foreach ($testData['input'] as $token) {
                $doctrineAccessTokenManager->save($token->getAccessToken());
                $em->persist($token);
            }

            $em->flush();

            $this->assertSame(4, $doctrineAccessTokenManager->clearRevoked());
        } finally {
            timecop_return();
        }

        $savedData = $em->getRepository(RefreshToken::class)->findBy(['revoked' => false], ['identifier' => 'ASC']);

        $this->assertSame(
            $testData['output'],
            $savedData
        );
    }

    private function buildRefreshToken(Client $client, string $identifier, string $modify, bool $revoked): RefreshToken
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
