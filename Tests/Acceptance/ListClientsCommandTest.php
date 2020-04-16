<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;
use Trikoder\Bundle\OAuth2Bundle\Model\RedirectUri;
use Trikoder\Bundle\OAuth2Bundle\Model\Scope;

final class ListClientsCommandTest extends AbstractAcceptanceTest
{
    public function testListClients(): void
    {
        $client = $this->fakeAClient('foobar');
        $this->getClientManager()->save($client);

        $command = $this->command();
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);
        $output = $commandTester->getDisplay();
        $expected = <<<'TABLE'
 ------------ -------- ------- -------------- ------------ 
  identifier   secret   scope   redirect uri   grant type  
 ------------ -------- ------- -------------- ------------ 
  foobar       quzbaz                                      
 ------------ -------- ------- -------------- ------------
TABLE;

        $this->assertEquals(trim($expected), trim($output));
    }

    public function testListClientsWithClientHavingNoSecret(): void
    {
        $client = $this->fakeAClient('foobar', null);
        $this->getClientManager()->save($client);

        $command = $this->command();
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);
        $output = $commandTester->getDisplay();
        $expected = <<<'TABLE'
 ------------ -------- ------- -------------- ------------ 
  identifier   secret   scope   redirect uri   grant type  
 ------------ -------- ------- -------------- ------------ 
  foobar                                                   
 ------------ -------- ------- -------------- ------------
TABLE;

        $this->assertEquals(trim($expected), trim($output));
    }

    public function testListClientsEmpty(): void
    {
        $command = $this->command();
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);
        $output = $commandTester->getDisplay();
        $expected = <<<'TABLE'
 ------------ -------- ------- -------------- ------------ 
  identifier   secret   scope   redirect uri   grant type  
 ------------ -------- ------- -------------- ------------
TABLE;

        $this->assertEquals(trim($expected), trim($output));
    }

    public function testListClientColumns(): void
    {
        $scopes = [
            new Scope('client-scope-1'),
            new Scope('client-scope-2'),
        ];

        $redirectUris = [
            new RedirectUri('http://client-redirect-url'),
        ];

        $client =
            $this
                ->fakeAClient('foobar')
                ->setScopes(...$scopes)
                ->setRedirectUris(...$redirectUris)
        ;
        $this->getClientManager()->save($client);

        $command = $this->command();
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--columns' => ['identifier', 'scope'],
        ]);
        $output = $commandTester->getDisplay();

        $expected = <<<'TABLE'
 ------------ -------------------------------- 
  identifier   scope                           
 ------------ -------------------------------- 
  foobar       client-scope-1, client-scope-2  
 ------------ --------------------------------
TABLE;

        $this->assertEquals(trim($expected), trim($output));
    }

    public function testListFiltersClients(): void
    {
        $clientA = $this->fakeAClient('client-a', 'client-a-secret');
        $this->getClientManager()->save($clientA);

        $clientB =
            $this
                ->fakeAClient('client-b', 'client-b-secret')
                ->setScopes(new Scope('client-b-scope'))
        ;
        $this->getClientManager()->save($clientB);

        $command = $this->command();
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--scope' => $clientB->getScopes(),
        ]);
        $output = $commandTester->getDisplay();

        $expected = <<<'TABLE'
 ------------ ----------------- ---------------- -------------- ------------ 
  identifier   secret            scope            redirect uri   grant type  
 ------------ ----------------- ---------------- -------------- ------------ 
  client-b     client-b-secret   client-b-scope                              
 ------------ ----------------- ---------------- -------------- ------------
TABLE;

        $this->assertEquals(trim($expected), trim($output));
    }

    private function fakeAClient($identifier, $secret = 'quzbaz'): Client
    {
        return new Client($identifier, $secret);
    }

    private function getClientManager(): ClientManagerInterface
    {
        return
            $this
                ->client
                ->getContainer()
                ->get(ClientManagerInterface::class)
            ;
    }

    private function command(): Command
    {
        return $this->application->find('trikoder:oauth2:list-clients');
    }
}
