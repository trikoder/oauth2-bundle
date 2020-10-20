<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Unit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Trikoder\Bundle\OAuth2Bundle\Manager\InMemory\AuthorizationCodeManager as InMemoryAuthCodeManager;
use Trikoder\Bundle\OAuth2Bundle\Model\AuthorizationCode;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;

final class InMemoryAuthCodeManagerTest extends TestCase
{
    public function testClearExpired(): void
    {
        $inMemoryAuthCodeManager = new InMemoryAuthCodeManager();

        timecop_freeze(new DateTimeImmutable());

        try {
            $testData = $this->buildTestData(
                function (array $item): bool {
                    return !$item['expired'];
                }
            );

            foreach ($testData['input'] as $token) {
                $inMemoryAuthCodeManager->save($token);
            }

            $this->assertSame(3, $inMemoryAuthCodeManager->clearExpired());
            $this->assertManagerContainsExpectedData($testData['output'], $inMemoryAuthCodeManager);
        } finally {
            timecop_return();
        }
    }

    public function testClearRevoked(): void
    {
        $inMemoryAuthCodeManager = new InMemoryAuthCodeManager();

        $testData = $this->buildTestData(
            function (array $item): bool {
                return !$item['revoked'];
            }
        );

        foreach ($testData['input'] as $token) {
            $inMemoryAuthCodeManager->save($token);
        }

        $this->assertSame(4, $inMemoryAuthCodeManager->clearRevoked());
        $this->assertManagerContainsExpectedData($testData['output'], $inMemoryAuthCodeManager);
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
            $authCode = $this->buildAuthCode(
                $identifier,
                $item['dateOffset'],
                $item['revoked']
            );
            $response['input'][$identifier] = $authCode;

            if ($successFunction($item)) {
                $response['output'][$identifier] = $authCode;
            }
        }

        return $response;
    }

    private function buildAuthCode(string $identifier, string $modify, bool $revoked): AuthorizationCode
    {
        $authorizationCode = new AuthorizationCode(
            $identifier,
            new DateTimeImmutable($modify),
            new Client('client', 'secret'),
            null,
            []
        );

        if ($revoked) {
            $authorizationCode->revoke();
        }

        return $authorizationCode;
    }

    private function assertManagerContainsExpectedData(array $output, InMemoryAuthCodeManager $inMemoryAuthCodeManager): void
    {
        $reflectionProperty = new ReflectionProperty(InMemoryAuthCodeManager::class, 'authorizationCodes');
        $reflectionProperty->setAccessible(true);

        $this->assertSame($output, $reflectionProperty->getValue($inMemoryAuthCodeManager));
    }
}
