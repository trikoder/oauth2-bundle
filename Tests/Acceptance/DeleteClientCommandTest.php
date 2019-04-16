<?php

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use Symfony\Component\Console\Tester\CommandTester;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;

final class DeleteClientCommandTest extends AbstractAcceptanceTest
{
    public function testDeleteClient()
    {
        $client = $this->fakeAClient('foobar');
        $this->getClientManager()->save($client);
        $this->assertCount(0, $client->getRedirectUris());
        $command = $this->command();
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'identifier' => $client->getIdentifier(),
        ]);
        $output = $commandTester->getDisplay();
        $this->assertContains('Given oAuth2 client deleted successfully', $output);
    }

    public function testDeleteNonExistentClient()
    {
        $identifierName = 'invalid identifier';
        $command = $this->command();
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'identifier' => $identifierName,
        ]);
        $output = $commandTester->getDisplay();
        $this->assertContains(sprintf('oAuth2 client identified as "%s" does not exist', $identifierName), $output);
    }

    private function fakeAClient($identifier): Client
    {
        return new Client($identifier, 'quzbaz');
    }

    private function getClientManager(): ClientManagerInterface
    {
        return $this->client
            ->getContainer()
            ->get(ClientManagerInterface::class)
            ;
    }

    private function command()
    {
        return $this->application->find('trikoder:oauth2:delete-client');
    }
}