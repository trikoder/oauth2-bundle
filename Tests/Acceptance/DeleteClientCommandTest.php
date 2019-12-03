<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Trikoder\Bundle\OAuth2Bundle\Manager\AccessTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\RefreshTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\AccessToken;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;
use Trikoder\Bundle\OAuth2Bundle\Model\RefreshToken;

/**
 * @covers \Trikoder\Bundle\OAuth2Bundle\Command\DeleteClientCommand
 */
final class DeleteClientCommandTest extends AbstractAcceptanceTest
{
    public function testDeleteClient(): void
    {
        $client = $this->fakeAClient('foobar');
        $this->getClientManager()->save($client);

        $command = $this->command();
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'identifier' => $client->getIdentifier(),
        ]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Given oAuth2 client deleted successfully', $output);

        $client = $this->findClient($client->getIdentifier());
        $this->assertNull($client);
    }

    public function testDeleteClientWithAccessTokens(): void
    {
        $client = $this->fakeAClient('foobar');
        $this->getClientManager()->save($client);

        $accessToken = $this->fakeAnAccessToken(
            'bazqux',
            'xyzzy',
            $client
        );
        $this->getAccessTokenManager()->save($accessToken);

        $command = $this->command();
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'identifier' => $client->getIdentifier(),
        ]);

        $this->clearEM();

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Given oAuth2 client deleted successfully', $output);

        $client = $this->findClient($client->getIdentifier());
        $this->assertNull($client);

        $accessToken = $this->findAccessToken($accessToken->getIdentifier());
        $this->assertNull($accessToken);
    }

    public function testDeleteClientWithAccessAndRefreshTokens(): void
    {
        $client = $this->fakeAClient('foobar');
        $this->getClientManager()->save($client);

        $accessToken = $this->fakeAnAccessToken(
            'bazqux',
            'xyzzy',
            $client
        );
        $this->getAccessTokenManager()->save($accessToken);

        $refreshToken = $this->fakeARefreshToken('killroy', $accessToken);
        $this->getRefreshTokenManager()->save($refreshToken);

        $command = $this->command();
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'identifier' => $client->getIdentifier(),
        ]);

        $this->clearEM();

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Given oAuth2 client deleted successfully', $output);

        $client = $this->findClient($client->getIdentifier());
        $this->assertNull($client);

        $accessToken = $this->findAccessToken($accessToken->getIdentifier());
        $this->assertNull($accessToken);

        $refreshToken = $this->findRefreshToken($refreshToken->getIdentifier());
        $this->assertNotNull($refreshToken);
        $this->assertNull($refreshToken->getAccessToken());
    }

    public function testDeleteNonExistentClient(): void
    {
        $identifierName = 'invalid identifier';
        $command = $this->command();
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'identifier' => $identifierName,
        ]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString(sprintf('oAuth2 client identified as "%s" does not exist', $identifierName), $output);
    }

    private function findClient(string $identifier): ?Client
    {
        return
            $this
                ->client
                ->getContainer()
                ->get(ClientManagerInterface::class)
                ->find($identifier)
            ;
    }

    private function findAccessToken(string $identifier): ?AccessToken
    {
        return
            $this
                ->client
                ->getContainer()
                ->get(AccessTokenManagerInterface::class)
                ->find($identifier)
            ;
    }

    private function findRefreshToken(string $identifier): ?RefreshToken
    {
        return
            $this
                ->client
                ->getContainer()
                ->get(RefreshTokenManagerInterface::class)
                ->find($identifier)
            ;
    }

    private function fakeAClient(string $identifier): Client
    {
        return new Client($identifier, 'quzbaz');
    }

    private function fakeAnAccessToken(
        string $identifier,
        string $userIdentifier,
        Client $client,
        array $scopes = [],
        string $timeModifier = '+1 day'
    ): AccessToken {
        return new AccessToken(
            $identifier,
            (new DateTime('now'))->modify($timeModifier),
            $client,
            $userIdentifier,
            $scopes
        );
    }

    private function fakeARefreshToken(string $identifier, AccessToken $accessToken, string $timeModifier = '+1 day')
    {
        return new RefreshToken(
            $identifier,
            (new DateTime('now'))->modify($timeModifier),
            $accessToken
        );
    }

    private function getClientManager(): ClientManagerInterface
    {
        return $this->client
            ->getContainer()
            ->get(ClientManagerInterface::class)
            ;
    }

    private function getAccessTokenManager(): AccessTokenManagerInterface
    {
        return
            $this
                ->client
                ->getContainer()
                ->get(AccessTokenManagerInterface::class)
            ;
    }

    private function getRefreshTokenManager(): RefreshTokenManagerInterface
    {
        return
            $this
                ->client
                ->getContainer()
                ->get(RefreshTokenManagerInterface::class)
            ;
    }

    private function command(): Command
    {
        return $this->application->find('trikoder:oauth2:delete-client');
    }

    private function clearEM()
    {
        $this
            ->client
            ->getContainer()
            ->get('doctrine')
            ->getManager()
            ->clear()
        ;
    }
}
