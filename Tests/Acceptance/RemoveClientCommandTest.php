<?php

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use Symfony\Component\Console\Tester\CommandTester;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;

final class RemoveClientCommandTest extends AbstractAcceptanceTest
{
    public function testRemoveClient()
    {
        $client = $this->fakeAClient('foobar');
        $this->getClientManager()->save($client);
        $this->assertCount(5, $this->getClientManager()->list());

        $command = $this->application->find('trikoder:oauth2:remove-client');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'identifier' => $client->getIdentifier(),
            '--force' => null
        ]);
        $output = $commandTester->getDisplay();
        $this->assertContains('Client foobar removed', $output);
        $this->assertCount(4, $this->getClientManager()->list());
    }

    public function testRemoveNotFoundClient()
    {
        $this->assertCount(4, $this->getClientManager()->list());

        $command = $this->application->find('trikoder:oauth2:remove-client');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'identifier' => 'doesntexist42',
        ]);
        $output = $commandTester->getDisplay();
        $this->assertContains('oAuth2 client identified as "doesntexist42" not found', $output);
        $this->assertCount(4, $this->getClientManager()->list());
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
