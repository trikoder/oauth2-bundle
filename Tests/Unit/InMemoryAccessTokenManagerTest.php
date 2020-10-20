<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Unit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Trikoder\Bundle\OAuth2Bundle\Manager\InMemory\AccessTokenManager as InMemoryAccessTokenManager;
use Trikoder\Bundle\OAuth2Bundle\Model\AccessToken;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;

final class InMemoryAccessTokenManagerTest extends TestCase
{
    public function testClearExpired(): void
    {
        $inMemoryAccessTokenManager = new InMemoryAccessTokenManager();

        timecop_freeze(new DateTimeImmutable());

        try {
            $testData = $this->buildTestData(
                function (array $item): bool {
                    return !$item['expired'];
                }
            );

            foreach ($testData['input'] as $token) {
                $inMemoryAccessTokenManager->save($token);
            }

            $this->assertSame(3, $inMemoryAccessTokenManager->clearExpired());
            $this->assertManagerContainsExpectedData($testData['output'], $inMemoryAccessTokenManager);
        } finally {
            timecop_return();
        }
    }

    public function testClearRevoked(): void
    {
        $inMemoryAccessTokenManager = new InMemoryAccessTokenManager();

        $testData = $this->buildTestData(
            function (array $item): bool {
                return !$item['revoked'];
            }
        );

        foreach ($testData['input'] as $token) {
            $inMemoryAccessTokenManager->save($token);
        }

        $this->assertSame(4, $inMemoryAccessTokenManager->clearRevoked());
        $this->assertManagerContainsExpectedData($testData['output'], $inMemoryAccessTokenManager);
    }

    private function buildTestData(callable $successFunction): array
    {
        $data = [
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
            ],
        ];

        $response = [];
        foreach ($data as $item) {
            $identifier = $item['identifier'];
            $accessToken = $this->buildAccessToken(
                $identifier,
                $item['dateOffset'],
                $item['revoked']
            );
            $response['input'][$identifier] = $accessToken;

            if ($successFunction($item)) {
                $response['output'][$identifier] = $accessToken;
            }
        }

        return $response;
    }

    private function buildAccessToken(string $identifier, string $modify, bool $revoked): AccessToken
    {
        $accessToken = new AccessToken(
            $identifier,
            new DateTimeImmutable($modify),
            new Client('client', 'secret'),
            null,
            []
        );

        if ($revoked) {
            $accessToken->revoke();
        }

        return $accessToken;
    }

    private function assertManagerContainsExpectedData(array $output, InMemoryAccessTokenManager $inMemoryAccessTokenManager): void
    {
        $reflectionProperty = new ReflectionProperty(InMemoryAccessTokenManager::class, 'accessTokens');
        $reflectionProperty->setAccessible(true);

        $this->assertSame($output, $reflectionProperty->getValue($inMemoryAccessTokenManager));
    }
}
