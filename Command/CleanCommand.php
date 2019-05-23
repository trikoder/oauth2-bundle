<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Trikoder\Bundle\OAuth2Bundle\Manager\AccessTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\RefreshTokenManagerInterface;

final class CleanCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'trikoder:oauth2:clean';

    /**
     * @var AccessTokenManagerInterface
     */
    private $accessTokenManager;

    /**
     * @var RefreshTokenManagerInterface
     */
    private $refreshTokenManager;

    /**
     * CleanCommand constructor.
     *
     * @param AccessTokenManagerInterface $accessTokenManager
     * @param RefreshTokenManagerInterface $refreshTokenManager
     */
    public function __construct(AccessTokenManagerInterface $accessTokenManager, RefreshTokenManagerInterface $refreshTokenManager)
    {
        parent::__construct();

        $this->accessTokenManager = $accessTokenManager;
        $this->refreshTokenManager = $refreshTokenManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Clean expired OAuth2 tokens');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach ([$this->refreshTokenManager, $this->accessTokenManager] as $manager) {
            $result = $manager->deleteExpired();
            $io->writeln(sprintf('Removed <info>%d</info> tokens from <comment>%s</comment>.', $result, get_class($manager)));
        }

        $io->success('OAuth2 tokens deleted successfully.');

        return 0;
    }
}
