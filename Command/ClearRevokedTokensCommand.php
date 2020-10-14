<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\AccessTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\AuthorizationCodeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\RefreshTokenManagerInterface;

final class ClearRevokedTokensCommand extends Command
{
    protected static $defaultName = 'trikoder:oauth2:clear-revoked-tokens';

    /**
     * @var AccessTokenManagerInterface
     */
    private $accessTokenManager;

    /**
     * @var RefreshTokenManagerInterface
     */
    private $refreshTokenManager;

    /**
     * @var AuthorizationCodeManagerInterface
     */
    private $authorizationCodeManager;

    public function __construct(
        AccessTokenManagerInterface $accessTokenManager,
        RefreshTokenManagerInterface $refreshTokenManager,
        AuthorizationCodeManagerInterface $authorizationCodeManager
    ) {
        parent::__construct();

        $this->accessTokenManager = $accessTokenManager;
        $this->refreshTokenManager = $refreshTokenManager;
        $this->authorizationCodeManager = $authorizationCodeManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Clears all revoked access and/or refresh tokens and/or auth codes')
            ->addOption(
                'access-tokens',
                'a',
                InputOption::VALUE_NONE,
                'Clear revoked access tokens.'
            )
            ->addOption(
                'refresh-tokens',
                'r',
                InputOption::VALUE_NONE,
                'Clear revoked refresh tokens.'
            )
            ->addOption(
                'auth-codes',
                'c',
                InputOption::VALUE_NONE,
                'Clear revoked auth codes.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $clearExpiredAccessTokens = $input->getOption('access-tokens');
        $clearExpiredRefreshTokens = $input->getOption('refresh-tokens');
        $clearExpiredAuthCodes = $input->getOption('auth-codes');

        if (!$clearExpiredAccessTokens && !$clearExpiredRefreshTokens && !$clearExpiredAuthCodes) {
            $clearExpiredAccessTokens = true;
            $clearExpiredRefreshTokens = true;
            $clearExpiredAuthCodes = true;
        }

        if (true === $clearExpiredAccessTokens) {
            $affected = $this->accessTokenManager->clearRevoked();
            $output->writeln(
                sprintf(
                    'Access tokens deleted: %s.',
                    $affected
                )
            );
        }

        if (true === $clearExpiredRefreshTokens) {
            $affected = $this->refreshTokenManager->clearRevoked();
            $output->writeln(
                sprintf(
                    'Refresh tokens deleted: %s.',
                    $affected
                )
            );
        }

        if (true === $clearExpiredAuthCodes) {
            $affected = $this->authorizationCodeManager->clearRevoked();
            $output->writeln(
                sprintf(
                    'Auth codes deleted: %s.',
                    $affected
                )
            );
        }

        return 0;
    }
}
