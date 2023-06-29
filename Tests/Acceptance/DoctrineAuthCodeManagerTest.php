<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use Doctrine\ORM\EntityManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\Doctrine\AuthorizationCodeManager as DoctrineAuthCodeManager;
use Trikoder\Bundle\OAuth2Bundle\Model\AuthorizationCode;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;

/**
 * @TODO   This should be in the Integration tests folder but the current tests infrastructure would need improvements first.
 *
 * @covers \Trikoder\Bundle\OAuth2Bundle\Manager\Doctrine\AuthorizationCodeManager
 */
final class DoctrineAuthCodeManagerTest extends AbstractAcceptanceTest
{
    public function testClearExpired(): void
    {
        /** @var EntityManagerInterface $em */
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $doctrineAuthCodeManager = new DoctrineAuthCodeManager($em);

        $client = new Client('client', 'secret');
        $em->persist($client);

        $testData = $this->buildClearExpiredTestData($client);

        /** @var AuthorizationCode $authCode */
        foreach ($testData['input'] as $authCode) {
            $doctrineAuthCodeManager->save($authCode);
        }

        $em->flush();

        $this->assertSame(3, $doctrineAuthCodeManager->clearExpired());

        $this->assertSame(
            $testData['output'],
            $em->getRepository(AuthorizationCode::class)->findBy([], ['identifier' => 'ASC'])
        );
    }

    private function buildClearExpiredTestData(Client $client): array
    {
        $validAuthCodes = [
            $this->buildAuthCode('1111', '+1 day', $client),
            $this->buildAuthCode('2222', '+1 hour', $client),
            $this->buildAuthCode('3333', '+5 second', $client),
        ];

        $expiredAuthCodes = [
            $this->buildAuthCode('5555', '-1 day', $client),
            $this->buildAuthCode('6666', '-1 hour', $client),
            $this->buildAuthCode('7777', '-1 second', $client),
        ];

        return [
            'input' => array_merge($validAuthCodes, $expiredAuthCodes),
            'output' => $validAuthCodes,
        ];
    }

    public function testClearRevoked(): void
    {
        /** @var EntityManagerInterface $em */
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $doctrineAuthCodeManager = new DoctrineAuthCodeManager($em);

        $client = new Client('client', 'secret');
        $em->persist($client);

        $testData = $this->buildClearRevokedTestData($client);

        /** @var AuthorizationCode $authCode */
        foreach ($testData['input'] as $authCode) {
            $doctrineAuthCodeManager->save($authCode);
        }

        $em->flush();

        $this->assertSame(2, $doctrineAuthCodeManager->clearRevoked());

        $this->assertSame(
            $testData['output'],
            $em->getRepository(AuthorizationCode::class)->findBy([], ['identifier' => 'ASC'])
        );
    }

    private function buildClearRevokedTestData(Client $client): array
    {
        $validAuthCodes = [
            $this->buildAuthCode('1111', '+1 day', $client),
            $this->buildAuthCode('2222', '-1 hour', $client),
            $this->buildAuthCode('3333', '+5 second', $client),
        ];

        $revokedAuthCodes = [
            $this->buildAuthCode('5555', '-1 day', $client, true),
            $this->buildAuthCode('6666', '+1 hour', $client, true),
        ];

        return [
            'input' => array_merge($validAuthCodes, $revokedAuthCodes),
            'output' => $validAuthCodes,
        ];
    }

    private function buildAuthCode(string $identifier, string $modify, Client $client, bool $revoked = false): AuthorizationCode
    {
        $authorizationCode = new AuthorizationCode(
            $identifier,
            new \DateTimeImmutable($modify),
            $client,
            null,
            []
        );

        if ($revoked) {
            $authorizationCode->revoke();
        }

        return $authorizationCode;
    }
}
