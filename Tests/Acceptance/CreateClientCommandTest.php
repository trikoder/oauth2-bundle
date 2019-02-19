<?php

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use Symfony\Component\Console\Tester\CommandTester;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;

final class CreateClientCommandTest extends AbstractAcceptanceTest
{
    public function testCreateClient()
    {
        $command = $this->application->find('trikoder:oauth2:create-client');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('New oAuth2 client created successfully', $output);
    }

    public function testCreateClientWithIdentifier()
    {
        $command = $this->application->find('trikoder:oauth2:create-client');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'identifier' => 'foobar',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('New oAuth2 client created successfully', $output);
        $this->assertContains('foobar', $output);

        $client = $this->client
            ->getContainer()
            ->get(ClientManagerInterface::class)
            ->find('foobar');
        $this->assertInstanceOf(Client::class, $client);
    }

    public function testCreateClientWithSecret()
    {
        $command = $this->application->find('trikoder:oauth2:create-client');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'identifier' => 'foobar',
            'secret' => 'quzbaz',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('New oAuth2 client created successfully', $output);
        $client = $this->client
            ->getContainer()
            ->get(ClientManagerInterface::class)
            ->find('foobar');
        $this->assertInstanceOf(Client::class, $client);
        $this->assertSame('quzbaz', $client->getSecret());
    }

    public function testCreateClientWithRedirectUris()
    {
        $command = $this->application->find('trikoder:oauth2:create-client');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'identifier' => 'foobar',
            '--redirect-uri' => ['http://example.org', 'http://example.org'],
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('New oAuth2 client created successfully', $output);
        $client = $this->client
            ->getContainer()
            ->get(ClientManagerInterface::class)
            ->find('foobar');
        $this->assertInstanceOf(Client::class, $client);
        $this->assertCount(2, $client->getRedirectUris());
    }

    public function testCreateClientWithGrantTypes()
    {
        $command = $this->application->find('trikoder:oauth2:create-client');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'identifier' => 'foobar',
            '--grant-type' => ['password', 'client_credentials'],
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('New oAuth2 client created successfully', $output);
        $client = $this->client
            ->getContainer()
            ->get(ClientManagerInterface::class)
            ->find('foobar');
        $this->assertInstanceOf(Client::class, $client);
        $this->assertCount(2, $client->getGrants());
    }

    public function testCreateClientWithScopes()
    {
        $command = $this->application->find('trikoder:oauth2:create-client');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'identifier' => 'foobar',
            '--scope' => ['foo', 'bar'],
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('New oAuth2 client created successfully', $output);
        $client = $this->client
            ->getContainer()
            ->get(ClientManagerInterface::class)
            ->find('foobar');
        $this->assertInstanceOf(Client::class, $client);
        $this->assertCount(2, $client->getScopes());
    }
}
