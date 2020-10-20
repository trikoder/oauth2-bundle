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
        $clearRevokedAccessTokens = $input->getOption('access-tokens');
        $clearRevokedRefreshTokens = $input->getOption('refresh-tokens');
        $clearRevokedAuthCodes = $input->getOption('auth-codes');

        if (!$clearRevokedAccessTokens && !$clearRevokedRefreshTokens && !$clearRevokedAuthCodes) {
            $clearRevokedAccessTokens = true;
            $clearRevokedRefreshTokens = true;
            $clearRevokedAuthCodes = true;
        }

        if (true === $clearRevokedAccessTokens && $this->clearRevokedMethodExists($output, $this->accessTokenManager)) {
            $affected = $this->accessTokenManager->clearRevoked();
            $output->writeln(
                sprintf(
                    'Access tokens deleted: %s.',
                    $affected
                )
            );
        }

        if (true === $clearRevokedRefreshTokens && $this->clearRevokedMethodExists($output, $this->refreshTokenManager)) {
            $affected = $this->refreshTokenManager->clearRevoked();
            $output->writeln(
                sprintf(
                    'Refresh tokens deleted: %s.',
                    $affected
                )
            );
        }

        if (true === $clearRevokedAuthCodes && $this->clearRevokedMethodExists($output, $this->authorizationCodeManager)) {
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

    private function clearRevokedMethodExists(OutputInterface $output, object $manager): bool
    {
        $methodName = 'clearRevoked';
        $exists = method_exists($manager, $methodName);

        if (!$exists) {
            $output->writeln(
                sprintf(
                    '<comment>Method "%s:%s()" will be required in the next major release. Skipping for now...</comment>',
                    \get_class($manager),
                    $methodName
                )
            );
        }

        return $exists;
    }
}
