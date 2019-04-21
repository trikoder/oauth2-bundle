<?php

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use Symfony\Component\Console\Tester\CommandTester;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;

final class GetClientCommandTest extends AbstractAcceptanceTest
{
    public function testGetClient()
    {
        $client = new Client('Bob', 'quzbaz');
        $this->getClientManager()->save($client);

        $command = $this->application->find('trikoder:oauth2:get-client');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'identifier' => $client->getIdentifier()
        ]);
        $output = $commandTester->getDisplay();
        $this->assertContains('Information for client Bob', $output);
    }

    public function testGetNotFoundClient()
    {
        $this->assertCount(4, $this->getClientManager()->list());

        $command = $this->application->find('trikoder:oauth2:get-client');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'identifier' => 'doesntexist42',
        ]);
        $output = $commandTester->getDisplay();
        $this->assertContains('oAuth2 client identified as "doesntexist42" not found', $output);
        $this->assertCount(4, $this->getClientManager()->list());
    }

    private function getClientManager(): ClientManagerInterface
    {
        return $this->client
            ->getContainer()
            ->get(ClientManagerInterface::class);
    }
}
