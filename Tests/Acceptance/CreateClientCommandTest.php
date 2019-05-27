<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use Symfony\Component\Console\Tester\CommandTester;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;

final class CreateClientCommandTest extends AbstractAcceptanceTest
{
    public function testCreateClient(): void
    {
        $command = $this->application->find('trikoder:oauth2:create-client');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('New oAuth2 client created successfully', $output);
    }

    public function testCreateClientWithIdentifier(): void
    {
        $command = $this->application->find('trikoder:oauth2:create-client');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'identifier' => 'foobar',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('New oAuth2 client created successfully', $output);
        $this->assertStringContainsString('foobar', $output);

        $client = $this->client
            ->getContainer()
            ->get(ClientManagerInterface::class)
            ->find('foobar');
        $this->assertInstanceOf(Client::class, $client);
    }

    public function testCreateClientWithSecret(): void
    {
        $command = $this->application->find('trikoder:oauth2:create-client');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'identifier' => 'foobar',
            'secret' => 'quzbaz',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('New oAuth2 client created successfully', $output);
        $client = $this->client
            ->getContainer()
            ->get(ClientManagerInterface::class)
            ->find('foobar');
        $this->assertInstanceOf(Client::class, $client);
        $this->assertSame('quzbaz', $client->getSecret());
    }

    public function testCreateClientWithRedirectUris(): void
    {
        $command = $this->application->find('trikoder:oauth2:create-client');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'identifier' => 'foobar',
            '--redirect-uri' => ['http://example.org', 'http://example.org'],
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('New oAuth2 client created successfully', $output);
        $client = $this->client
            ->getContainer()
            ->get(ClientManagerInterface::class)
            ->find('foobar');
        $this->assertInstanceOf(Client::class, $client);
        $this->assertCount(2, $client->getRedirectUris());
    }

    public function testCreateClientWithGrantTypes(): void
    {
        $command = $this->application->find('trikoder:oauth2:create-client');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'identifier' => 'foobar',
            '--grant-type' => ['password', 'client_credentials'],
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('New oAuth2 client created successfully', $output);
        $client = $this->client
            ->getContainer()
            ->get(ClientManagerInterface::class)
            ->find('foobar');
        $this->assertInstanceOf(Client::class, $client);
        $this->assertCount(2, $client->getGrants());
    }

    public function testCreateClientWithScopes(): void
    {
        $command = $this->application->find('trikoder:oauth2:create-client');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'identifier' => 'foobar',
            '--scope' => ['foo', 'bar'],
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('New oAuth2 client created successfully', $output);
        $client = $this->client
            ->getContainer()
            ->get(ClientManagerInterface::class)
            ->find('foobar');
        $this->assertInstanceOf(Client::class, $client);
        $this->assertCount(2, $client->getScopes());
    }
}
