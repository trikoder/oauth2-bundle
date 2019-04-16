<?php

namespace Trikoder\Bundle\OAuth2Bundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;

final class DeleteClientCommand extends Command
{
    protected static $defaultName = 'trikoder:oauth2:delete-client';
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $client = $this->entityManager->find(Client::class, $input->getArgument('identifier'));
        if (null === $client) {
            $io->error(sprintf('oAuth2 client identified as "%s" does not exist', $input->getArgument('identifier')));
            return 1;
        }
        $this->entityManager->remove($client);
        $this->entityManager->flush();
        $io->success('Given oAuth2 client deleted successfully.');
        return 0;
    }
}
