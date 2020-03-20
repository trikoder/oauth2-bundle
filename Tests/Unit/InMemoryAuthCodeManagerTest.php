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
            $testData = $this->buildClearExpiredTestData();

            /** @var AuthorizationCode $authCode */
            foreach ($testData['input'] as $authCode) {
                $inMemoryAuthCodeManager->save($authCode);
            }

            $this->assertSame(3, $inMemoryAuthCodeManager->clearExpired());
        } finally {
            timecop_return();
        }

        $reflectionProperty = new ReflectionProperty(InMemoryAuthCodeManager::class, 'authorizationCodes');
        $reflectionProperty->setAccessible(true);

        $this->assertSame($testData['output'], $reflectionProperty->getValue($inMemoryAuthCodeManager));
    }

    private function buildClearExpiredTestData(): array
    {
        $validAuthCodes = [
            '1111' => $this->buildAuthCode('1111', '+1 day'),
            '2222' => $this->buildAuthCode('2222', '+1 hour'),
            '3333' => $this->buildAuthCode('3333', '+1 second'),
            '4444' => $this->buildAuthCode('4444', 'now'),
        ];

        $expiredAuthCodes = [
            '5555' => $this->buildAuthCode('5555', '-1 day'),
            '6666' => $this->buildAuthCode('6666', '-1 hour'),
            '7777' => $this->buildAuthCode('7777', '-1 second'),
        ];

        return [
            'input' => $validAuthCodes + $expiredAuthCodes,
            'output' => $validAuthCodes,
        ];
    }

    private function buildAuthCode(string $identifier, string $modify): AuthorizationCode
    {
        return new AuthorizationCode(
            $identifier,
            new DateTimeImmutable($modify),
            new Client('client', 'secret'),
            null,
            []
        );
    }
}
