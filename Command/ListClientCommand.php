<?php

namespace Trikoder\Bundle\OAuth2Bundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;

final class ListClientCommand extends Command
{
    protected static $defaultName = 'trikoder:oauth2:list-client';

    private $clientManager;

    public function __construct(ClientManagerInterface $clientManager)
    {
        parent::__construct();

        $this->clientManager = $clientManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('List all oAuth2 clients')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $clientList = $this->clientManager->list();
        $io->success('List of all oAuth2 clients.');

        $headers = ['Identifier', 'Scopes', 'Active'];
        $rows = [];
        foreach ($clientList as $client) {
            $rows[] = [$client->getIdentifier(), implode("\n", $client->getScopes()), $client->isActive()];
        }
        $io->table($headers, $rows);

        return 0;
    }
}
