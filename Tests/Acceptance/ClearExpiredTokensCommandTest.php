<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Trikoder\Bundle\OAuth2Bundle\Manager\AccessTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\AuthorizationCodeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\RefreshTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ScopeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\AccessToken;
use Trikoder\Bundle\OAuth2Bundle\Model\AuthorizationCode;
use Trikoder\Bundle\OAuth2Bundle\Model\RefreshToken;
use Trikoder\Bundle\OAuth2Bundle\Tests\Fixtures\FixtureFactory;

final class ClearExpiredTokensCommandTest extends AbstractAcceptanceTest
{
    protected function setUp(): void
    {
        parent::setUp();

        timecop_freeze(new DateTimeImmutable());

        FixtureFactory::initializeFixtures(
            $this->client->getContainer()->get(ScopeManagerInterface::class),
            $this->client->getContainer()->get(ClientManagerInterface::class),
            $this->client->getContainer()->get(AccessTokenManagerInterface::class),
            $this->client->getContainer()->get(RefreshTokenManagerInterface::class),
            $this->client->getContainer()->get(AuthorizationCodeManagerInterface::class)
        );
    }

    protected function tearDown(): void
    {
        timecop_return();

        parent::tearDown();
    }

    public function testClearExpiredAccessAndRefreshTokensAndAuthCodes(): void
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
        $this->assertStringContainsString('Cleared 1 expired auth code.', $output);

        /** @var EntityManagerInterface $em */
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $em->clear();

        $this->assertNull(
            $this->client->getContainer()->get(AccessTokenManagerInterface::class)->find(
                FixtureFactory::FIXTURE_ACCESS_TOKEN_EXPIRED
            )
        );
        $this->assertNull(
            $this->client->getContainer()->get(RefreshTokenManagerInterface::class)->find(
                FixtureFactory::FIXTURE_REFRESH_TOKEN_EXPIRED
            )
        );
        $this->assertNull(
            $this->client->getContainer()->get(AuthorizationCodeManagerInterface::class)->find(
                FixtureFactory::FIXTURE_AUTH_CODE_EXPIRED
            )
        );
    }

    public function testClearExpiredAccessTokens(): void
    {
        $command = $this->command();
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'command' => $command->getName(),
            '--access-tokens' => true,
        ]);

        $this->assertSame(0, $exitCode);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Cleared 1 expired access token.', $output);
        $this->assertStringNotContainsString('Cleared 1 expired refresh token.', $output);
        $this->assertStringNotContainsString('Cleared 1 expired auth code.', $output);

        /** @var EntityManagerInterface $em */
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $em->clear();

        $this->assertNull(
            $this->client->getContainer()->get(AccessTokenManagerInterface::class)->find(
                FixtureFactory::FIXTURE_ACCESS_TOKEN_EXPIRED
            )
        );
        $this->assertInstanceOf(
            RefreshToken::class,
            $this->client->getContainer()->get(RefreshTokenManagerInterface::class)->find(
                FixtureFactory::FIXTURE_REFRESH_TOKEN_EXPIRED
            )
        );
        $this->assertInstanceOf(
            AuthorizationCode::class,
            $this->client->getContainer()->get(AuthorizationCodeManagerInterface::class)->find(
                FixtureFactory::FIXTURE_AUTH_CODE_EXPIRED
            )
        );
    }

    public function testClearExpiredRefreshTokens(): void
    {
        $command = $this->command();
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'command' => $command->getName(),
            '--refresh-tokens' => true,
        ]);

        $this->assertSame(0, $exitCode);

        $output = $commandTester->getDisplay();
        $this->assertStringNotContainsString('Cleared 1 expired access token.', $output);
        $this->assertStringContainsString('Cleared 1 expired refresh token.', $output);
        $this->assertStringNotContainsString('Cleared 1 expired auth code.', $output);

        /** @var EntityManagerInterface $em */
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $em->clear();

        $this->assertInstanceOf(
            AccessToken::class,
            $this->client->getContainer()->get(AccessTokenManagerInterface::class)->find(
                FixtureFactory::FIXTURE_ACCESS_TOKEN_EXPIRED
            )
        );
        $this->assertNull(
            $this->client->getContainer()->get(RefreshTokenManagerInterface::class)->find(
                FixtureFactory::FIXTURE_REFRESH_TOKEN_EXPIRED
            )
        );
        $this->assertInstanceOf(
            AuthorizationCode::class,
            $this->client->getContainer()->get(AuthorizationCodeManagerInterface::class)->find(
                FixtureFactory::FIXTURE_AUTH_CODE_EXPIRED
            )
        );
    }

    public function testClearExpiredAuthCodes(): void
    {
        $command = $this->command();
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'command' => $command->getName(),
            '--auth-codes' => true,
        ]);

        $this->assertSame(0, $exitCode);

        $output = $commandTester->getDisplay();
        $this->assertStringNotContainsString('Cleared 1 expired access token.', $output);
        $this->assertStringNotContainsString('Cleared 1 expired refresh token.', $output);
        $this->assertStringContainsString('Cleared 1 expired auth code.', $output);

        /** @var EntityManagerInterface $em */
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $em->clear();

        $this->assertInstanceOf(
            AccessToken::class,
            $this->client->getContainer()->get(AccessTokenManagerInterface::class)->find(
                FixtureFactory::FIXTURE_ACCESS_TOKEN_EXPIRED
            )
        );
        $this->assertInstanceOf(
            RefreshToken::class,
            $this->client->getContainer()->get(RefreshTokenManagerInterface::class)->find(
                FixtureFactory::FIXTURE_REFRESH_TOKEN_EXPIRED
            )
        );
        $this->assertNull(
            $this->client->getContainer()->get(AuthorizationCodeManagerInterface::class)->find(
                FixtureFactory::FIXTURE_AUTH_CODE_EXPIRED
            )
        );
    }

    private function command(): Command
    {
        return $this->application->find('trikoder:oauth2:clear-expired-tokens');
    }
}
