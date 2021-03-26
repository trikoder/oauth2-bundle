<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;
use Trikoder\Bundle\OAuth2Bundle\Model\Grant;
use Trikoder\Bundle\OAuth2Bundle\Model\RedirectUri;
use Trikoder\Bundle\OAuth2Bundle\Model\Scope;

final class UpdateClientCommand extends Command
{
    protected static $defaultName = 'trikoder:oauth2:update-client';

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
            ->setDescription('Updates an oAuth2 client')
            ->addOption(
                'redirect-uri',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Sets redirect uri for client. Use this option multiple times to set multiple redirect URIs. Use it without value to remove existing values.',
                [0]
            )
            ->addOption(
                'grant-type',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Sets allowed grant type for client. Use this option multiple times to set multiple grant types. Use it without value to remove existing values.',
                [0]
            )
            ->addOption(
                'scope',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Sets allowed scope for client. Use this option multiple times to set multiple scopes. Use it without value to remove existing values.',
                [0]
            )
            ->addOption(
                'active',
                null,
                InputOption::VALUE_REQUIRED,
                'Client active state, 1 for active, 0 for inactive',
                null
            )
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

        if (null === $client = $this->clientManager->find($input->getArgument('identifier'))) {
            $io->error(sprintf('oAuth2 client identified as "%s"', $input->getArgument('identifier')));

            return 1;
        }

        $client = $this->updateClientFromInput($client, $input);
        $this->clientManager->save($client);
        $io->success('Given oAuth2 client updated successfully.');

        return 0;
    }

    private function updateClientFromInput(Client $client, InputInterface $input): Client
    {
        $active = $input->getOption('active');

        if (null !== $active) {
            $client->setActive((bool) $active);
        }

        $redirectUrisArray = $this->getNullableOption($input, 'redirect-uri');

        if (null !== $redirectUrisArray) {
            $redirectUris = array_map(
                static function (string $redirectUri): RedirectUri {
                    return new RedirectUri($redirectUri);
                },
                $redirectUrisArray
            );
            $client->setRedirectUris(...$redirectUris);
        }

        $grantsArray = $this->getNullableOption($input, 'grant-type');

        if (null !== $grantsArray) {
            $grants = array_map(
                static function (string $grant): Grant {
                    return new Grant($grant);
                },
                $grantsArray
            );
            $client->setGrants(...$grants);
        }

        $scopesArray = $this->getNullableOption($input, 'scope');

        if (null !== $scopesArray) {
            $scopes = array_map(
                static function (string $scope): Scope {
                    return new Scope($scope);
                },
                $scopesArray
            );
            $client->setScopes(...$scopes);
        }

        return $client;
    }

    private function getNullableOption(InputInterface $input, string $name): ?array
    {
        $value = $input->getOption($name);

        if (
            \array_key_exists(0, $value)
            && 0 === $value[0] //if user has entered some value it will always be string so it is fine to rely on 0
        ) {
            return null;
        }

        if (
            \array_key_exists(0, $value)
            && null === $value[0] //when option has mode InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY and no value is sent, option will have value [null]
        ) {
            return [];
        }

        return $value;
    }
}
