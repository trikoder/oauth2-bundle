<?php

namespace Trikoder\Bundle\OAuth2Bundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;

final class RemoveClientCommand extends Command
{
    protected static $defaultName = 'trikoder:oauth2:remove-client';

    private $clientManager;

    public function __construct(ClientManagerInterface $clientManager)
    {
        parent::__construct();

        $this->clientManager = $clientManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Remove an oAuth2 client')
            ->addArgument(
                'identifier',
                InputArgument::REQUIRED,
                'The client ID'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Delete the client whiteout asking',
                false
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

        $this->clientManager->remove($client);

        $io->success("Client {$client->getIdentifier()} removed.");

        return 0;
    }
}
