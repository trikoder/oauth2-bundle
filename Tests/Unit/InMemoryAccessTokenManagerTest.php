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
            $testData = $this->buildClearExpiredTestData();

            foreach ($testData['input'] as $token) {
                $inMemoryAccessTokenManager->save($token);
            }

            $this->assertSame(3, $inMemoryAccessTokenManager->clearExpired());
            $this->assertManagerContainsExpectedData($testData['output'], $inMemoryAccessTokenManager);
        } finally {
            timecop_return();
        }
    }

    private function buildClearExpiredTestData(): array
    {
        $validAccessTokens = [
            '1111' => $this->buildAccessToken('1111', '+1 day'),
            '2222' => $this->buildAccessToken('2222', '+1 hour'),
            '3333' => $this->buildAccessToken('3333', '+1 second'),
            '4444' => $this->buildAccessToken('4444', 'now'),
        ];

        $expiredAccessTokens = [
            '5555' => $this->buildAccessToken('5555', '-1 day'),
            '6666' => $this->buildAccessToken('6666', '-1 hour'),
            '7777' => $this->buildAccessToken('7777', '-1 second'),
        ];

        return [
            'input' => $validAccessTokens + $expiredAccessTokens,
            'output' => $validAccessTokens,
        ];
    }

    public function testClearRevoked(): void
    {
        $inMemoryAccessTokenManager = new InMemoryAccessTokenManager();

        $testData = $this->buildClearRevokedTestData();

        foreach ($testData['input'] as $token) {
            $inMemoryAccessTokenManager->save($token);
        }

        $this->assertSame(2, $inMemoryAccessTokenManager->clearRevoked());
        $this->assertManagerContainsExpectedData($testData['output'], $inMemoryAccessTokenManager);
    }

    private function buildClearRevokedTestData(): array
    {
        $validAccessTokens = [
            '1111' => $this->buildAccessToken('1111', '+1 day'),
            '2222' => $this->buildAccessToken('2222', '-1 hour'),
            '3333' => $this->buildAccessToken('3333', '+1 second'),
        ];

        $revokedAccessTokens = [
            '5555' => $this->buildAccessToken('5555', '-1 day', true),
            '6666' => $this->buildAccessToken('6666', '+1 hour', true),
        ];

        return [
            'input' => $validAccessTokens + $revokedAccessTokens,
            'output' => $validAccessTokens,
        ];
    }

    private function buildAccessToken(string $identifier, string $modify, bool $revoked = false): AccessToken
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
