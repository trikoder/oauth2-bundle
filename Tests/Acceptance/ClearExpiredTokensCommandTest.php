<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Trikoder\Bundle\OAuth2Bundle\Manager\AccessTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\RefreshTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ScopeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Tests\Fixtures\FixtureFactory;

final class ClearExpiredTokensCommandTest extends AbstractAcceptanceTest
{
    protected function setUp(): void
    {
        parent::setUp();

        timecop_freeze(new DateTime());

        FixtureFactory::initializeFixtures(
            $this->client->getContainer()->get(ScopeManagerInterface::class),
            $this->client->getContainer()->get(ClientManagerInterface::class),
            $this->client->getContainer()->get(AccessTokenManagerInterface::class),
            $this->client->getContainer()->get(RefreshTokenManagerInterface::class)
        );
    }

    protected function tearDown(): void
    {
        timecop_return();

        parent::tearDown();
    }

    public function testClearExpiredAccessAndRefreshTokens(): void
    {
        $command = $this->command();
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $this->assertSame(0, $exitCode);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Cleared 1 expired access token.', $output);
        $this->assertStringContainsString('Cleared 1 expired refresh token.', $output);
    }

    public function testClearExpiredAccessTokens(): void
    {
        $command = $this->command();
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'command' => $command->getName(),
            '--access-tokens-only' => true,
        ]);

        $this->assertSame(0, $exitCode);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Cleared 1 expired access token.', $output);
        $this->assertStringNotContainsString('Cleared 1 expired refresh token.', $output);
    }

    public function testClearExpiredRefreshTokens(): void
    {
        $command = $this->command();
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'command' => $command->getName(),
            '--refresh-tokens-only' => true,
        ]);

        $this->assertSame(0, $exitCode);

        $output = $commandTester->getDisplay();
        $this->assertStringNotContainsString('Cleared 1 expired access token.', $output);
        $this->assertStringContainsString('Cleared 1 expired refresh token.', $output);
    }

    public function testErrorWhenBothOptionsAreUsed(): void
    {
        $command = $this->command();
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'command' => $command->getName(),
            '--access-tokens-only' => true,
            '--refresh-tokens-only' => true,
        ]);

        $this->assertSame(1, $exitCode);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Please choose only one of the following options:', $output);
    }

    private function command(): Command
    {
        return $this->application->find('trikoder:oauth2:clear-expired-tokens');
    }
}
