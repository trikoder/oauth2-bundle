<?php

namespace Trikoder\Bundle\OAuth2Bundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;

final class GetClientCommand extends Command
{
    protected static $defaultName = 'trikoder:oauth2:get-client';

    private $clientManager;

    public function __construct(ClientManagerInterface $clientManager)
    {
        parent::__construct();

        $this->clientManager = $clientManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Get oAuth2 client information')
            ->addArgument(
                'identifier',
                InputArgument::REQUIRED,
                'The client ID'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $client = $this->clientManager->find($input->getArgument('identifier'));

        if (!$client) {
            $io->error(sprintf('oAuth2 client identified as "%s" not found', $input->getArgument('identifier')));

            return 1;
        }

        $io->success("Information for client {$client->getIdentifier()}.");

        if ($client->isActive()) {
            $io->warning('Client is currently disabled');
        }

        $io->section('Secret');
        $io->text($client->getSecret());

        $io->section('Grants');
        $io->text($client->getGrants());

        $io->section('Scopes');
        $io->text($client->getScopes());

        $io->section('RedirectUris');
        $io->text($client->getRedirectUris());

        return 0;
    }
}
