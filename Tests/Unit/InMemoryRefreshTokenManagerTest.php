<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Unit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Trikoder\Bundle\OAuth2Bundle\Manager\InMemory\RefreshTokenManager as InMemoryRefreshTokenManager;
use Trikoder\Bundle\OAuth2Bundle\Model\AccessToken;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;
use Trikoder\Bundle\OAuth2Bundle\Model\RefreshToken;

final class InMemoryRefreshTokenManagerTest extends TestCase
{
    public function testClearExpired(): void
    {
        $inMemoryRefreshTokenManager = new InMemoryRefreshTokenManager();

        timecop_freeze(new DateTimeImmutable());

        try {
            $testData = $this->buildTestData(
                function (array $item): bool {
                    return !$item['expired'];
                }
            );

            foreach ($testData['input'] as $token) {
                $inMemoryRefreshTokenManager->save($token);
            }

            $this->assertSame(3, $inMemoryRefreshTokenManager->clearExpired());
            $this->compareOutput($testData['output'], $inMemoryRefreshTokenManager);
        } finally {
            timecop_return();
        }
    }

    public function testClearRevoked(): void
    {
        $inMemoryRefreshTokenManager = new InMemoryRefreshTokenManager();

        timecop_freeze(new DateTimeImmutable());

        try {
            $testData = $this->buildTestData(
                function (array $item): bool {
                    return !$item['revoked'];
                }
            );

            foreach ($testData['input'] as $token) {
                $inMemoryRefreshTokenManager->save($token);
            }

            $this->assertSame(4, $inMemoryRefreshTokenManager->clearRevoked());
            $this->compareOutput($testData['output'], $inMemoryRefreshTokenManager);
        } finally {
            timecop_return();
        }
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
            ]
        ];

        $response = [];
        foreach ($data as $item) {
            $identifier = $item['identifier'];
            $RefreshToken = $this->buildRefreshToken(
                $identifier,
                $item['dateOffset'],
                $item['revoked']
            );
            $response['input'][$identifier] = $RefreshToken;

            if ($successFunction($item)) {
                $response['output'][$identifier] = $RefreshToken;
            }
        }

        return $response;
    }

    private function buildRefreshToken(string $identifier, string $modify, bool $revoked): RefreshToken
    {
        $refreshToken = new RefreshToken(
            $identifier,
            new DateTimeImmutable($modify),
            new AccessToken(
                $identifier,
                new DateTimeImmutable('+1 day'),
                new Client('client', 'secret'),
                null,
                []
            )
        );

        if ($revoked) {
            $refreshToken->revoke();
        }

        return $refreshToken;
    }

    private function compareOutput(array $output, InMemoryRefreshTokenManager $inMemoryRefreshTokenManager): void
    {
        $reflectionProperty = new ReflectionProperty(InMemoryRefreshTokenManager::class, 'refreshTokens');
        $reflectionProperty->setAccessible(true);

        $this->assertSame($output, $reflectionProperty->getValue($inMemoryRefreshTokenManager));
    }
}
