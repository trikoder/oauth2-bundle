<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;

final class DeleteClientCommand extends Command
{
    protected static $defaultName = 'trikoder:oauth2:delete-client';

    /**
     * @var ClientManagerInterface
     */
    private $clientManager;

    public function __construct(ClientManagerInterface $clientManager)
    {
        parent::__construct();

        $this->clientManager = $clientManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Deletes an oAuth2 client')
            ->addArgument(
                'identifier',
                InputArgument::REQUIRED,
                'The client ID'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $identifier = $input->getArgument('identifier');
        $client = $this->clientManager->find($identifier);
        if (null === $client) {
            $io->error(sprintf('oAuth2 client identified as "%s" does not exist', $identifier));

            return 1;
        }
        $this->clientManager->remove($client);
        $io->success('Given oAuth2 client deleted successfully.');

        return 0;
    }
}
