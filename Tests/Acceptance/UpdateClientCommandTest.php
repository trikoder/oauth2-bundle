<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use Symfony\Component\Console\Tester\CommandTester;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;

final class UpdateClientCommandTest extends AbstractAcceptanceTest
{
    public function testUpdateRedirectUris(): void
    {
        $client = $this->fakeAClient('foobar');
        $this->getClientManager()->save($client);
        $this->assertCount(0, $client->getRedirectUris());

        $command = $this->application->find('trikoder:oauth2:update-client');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'identifier' => $client->getIdentifier(),
            '--redirect-uri' => ['http://example.com', 'http://example.org'],
        ]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Given oAuth2 client updated successfully', $output);
        $this->assertCount(2, $client->getRedirectUris());
    }

    public function testUpdateGrantTypes(): void
    {
        $client = $this->fakeAClient('foobar');
        $this->getClientManager()->save($client);
        $this->assertCount(0, $client->getGrants());

        $command = $this->application->find('trikoder:oauth2:update-client');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'identifier' => $client->getIdentifier(),
            '--grant-type' => ['password', 'client_credentials'],
        ]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Given oAuth2 client updated successfully', $output);
        $this->assertCount(2, $client->getGrants());
    }

    public function testUpdateScopes(): void
    {
        $client = $this->fakeAClient('foobar');
        $this->getClientManager()->save($client);
        $this->assertCount(0, $client->getScopes());

        $command = $this->application->find('trikoder:oauth2:update-client');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'identifier' => $client->getIdentifier(),
            '--scope' => ['foo', 'bar'],
        ]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Given oAuth2 client updated successfully', $output);
        $this->assertCount(2, $client->getScopes());
    }

    public function testDeactivate(): void
    {
        $client = $this->fakeAClient('foobar');
        $this->getClientManager()->save($client);
        $this->assertTrue($client->isActive());

        $command = $this->application->find('trikoder:oauth2:update-client');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'identifier' => $client->getIdentifier(),
            '--deactivated' => true,
        ]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Given oAuth2 client updated successfully', $output);
        $updatedClient = $this->getClientManager()->find($client->getIdentifier());
        $this->assertFalse($updatedClient->isActive());
    }

    private function fakeAClient($identifier): Client
    {
        return new Client($identifier, 'quzbaz');
    }

    private function getClientManager(): ClientManagerInterface
    {
        return $this->client
            ->getContainer()
            ->get(ClientManagerInterface::class);
    }
}
