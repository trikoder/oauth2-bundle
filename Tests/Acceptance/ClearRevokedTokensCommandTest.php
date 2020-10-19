<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use Doctrine\ORM\EntityManagerInterface;
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

final class ClearRevokedTokensCommandTest extends AbstractAcceptanceTest
{
    protected function setUp(): void
    {
        parent::setUp();

        timecop_freeze(new \DateTimeImmutable());

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

    public function testClearRevokedAccessAndRefreshTokensAndAuthCodes(): void
    {
        $this->assertNotNull(
            $this->client->getContainer()->get(AccessTokenManagerInterface::class)->find(
                FixtureFactory::FIXTURE_ACCESS_TOKEN_REVOKED
            )
        );
        $this->assertNotNull(
            $this->client->getContainer()->get(RefreshTokenManagerInterface::class)->find(
                FixtureFactory::FIXTURE_REFRESH_TOKEN_REVOKED
            )
        );
        $this->assertNotNull(
            $this->client->getContainer()->get(AuthorizationCodeManagerInterface::class)->find(
                FixtureFactory::FIXTURE_AUTH_CODE_REVOKED
            )
        );

        $output = $this->executeCommand();

        $this->assertStringContainsString('Access tokens deleted: 1.', $output);
        $this->assertStringContainsString('Refresh tokens deleted: 1.', $output);
        $this->assertStringContainsString('Auth codes deleted: 1.', $output);

        $this->assertNull(
            $this->client->getContainer()->get(AccessTokenManagerInterface::class)->find(
                FixtureFactory::FIXTURE_ACCESS_TOKEN_REVOKED
            )
        );
        $this->assertNull(
            $this->client->getContainer()->get(RefreshTokenManagerInterface::class)->find(
                FixtureFactory::FIXTURE_REFRESH_TOKEN_REVOKED
            )
        );
        $this->assertNull(
            $this->client->getContainer()->get(AuthorizationCodeManagerInterface::class)->find(
                FixtureFactory::FIXTURE_AUTH_CODE_REVOKED
            )
        );
    }

    public function testClearRevokedAccessTokens(): void
    {
        $output = $this->executeCommand([
            '--access-tokens' => true,
        ]);

        $this->assertStringContainsString('Access tokens deleted: 1.', $output);
        $this->assertStringNotContainsString('Refresh tokens deleted', $output);
        $this->assertStringNotContainsString('Auth codes deleted', $output);

        $this->assertNull(
            $this->client->getContainer()->get(AccessTokenManagerInterface::class)->find(
                FixtureFactory::FIXTURE_ACCESS_TOKEN_REVOKED
            )
        );
        $this->assertInstanceOf(
            RefreshToken::class,
            $this->client->getContainer()->get(RefreshTokenManagerInterface::class)->find(
                FixtureFactory::FIXTURE_REFRESH_TOKEN_REVOKED
            )
        );
        $this->assertInstanceOf(
            AuthorizationCode::class,
            $this->client->getContainer()->get(AuthorizationCodeManagerInterface::class)->find(
                FixtureFactory::FIXTURE_AUTH_CODE_REVOKED
            )
        );
    }

    public function testClearRevokedRefreshTokens(): void
    {
        $output = $this->executeCommand([
            '--refresh-tokens' => true,
        ]);

        $this->assertStringNotContainsString('Access tokens deleted', $output);
        $this->assertStringContainsString('Refresh tokens deleted: 1.', $output);
        $this->assertStringNotContainsString('Auth codes deleted', $output);

        $this->assertInstanceOf(
            AccessToken::class,
            $this->client->getContainer()->get(AccessTokenManagerInterface::class)->find(
                FixtureFactory::FIXTURE_ACCESS_TOKEN_REVOKED
            )
        );
        $this->assertNull(
            $this->client->getContainer()->get(RefreshTokenManagerInterface::class)->find(
                FixtureFactory::FIXTURE_REFRESH_TOKEN_REVOKED
            )
        );
        $this->assertInstanceOf(
            AuthorizationCode::class,
            $this->client->getContainer()->get(AuthorizationCodeManagerInterface::class)->find(
                FixtureFactory::FIXTURE_AUTH_CODE_REVOKED
            )
        );
    }

    public function testClearRevokedAuthCodes(): void
    {
        $output = $this->executeCommand([
            '--auth-codes' => true,
        ]);

        $this->assertStringNotContainsString('Access tokens deleted', $output);
        $this->assertStringNotContainsString('Refresh tokens deleted', $output);
        $this->assertStringContainsString('Auth codes deleted: 1.', $output);

        $this->assertInstanceOf(
            AccessToken::class,
            $this->client->getContainer()->get(AccessTokenManagerInterface::class)->find(
                FixtureFactory::FIXTURE_ACCESS_TOKEN_REVOKED
            )
        );
        $this->assertInstanceOf(
            RefreshToken::class,
            $this->client->getContainer()->get(RefreshTokenManagerInterface::class)->find(
                FixtureFactory::FIXTURE_REFRESH_TOKEN_REVOKED
            )
        );
        $this->assertNull(
            $this->client->getContainer()->get(AuthorizationCodeManagerInterface::class)->find(
                FixtureFactory::FIXTURE_AUTH_CODE_REVOKED
            )
        );
    }

    private function executeCommand(array $params = [], int $expectedExitCode = 0): string
    {
        $command = $this->application->find('trikoder:oauth2:clear-revoked-tokens');
        $commandTester = new CommandTester($command);
        $exitCode = $commandTester->execute(array_merge(
            [
                'command' => $command->getName(),
            ],
            $params
        ));
        $this->assertSame($expectedExitCode, $exitCode);
        $this->clearEntityManager();

        return $commandTester->getDisplay(true);
    }

    private function clearEntityManager(): void
    {
        /** @var EntityManagerInterface $em */
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $em->clear();
    }
}
