<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Command;

use InvalidArgumentException;
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

final class CreateClientCommand extends Command
{
    protected static $defaultName = 'trikoder:oauth2:create-client';

    /**
     * @var ClientManagerInterface
     */
    private $clientManager;

    /**
     * @var bool
     */
    private $cryptClientSecret;

    public function __construct(ClientManagerInterface $clientManager, bool $cryptClientSecret = false)
    {
        parent::__construct();

        $this->clientManager = $clientManager;
        $this->cryptClientSecret = $cryptClientSecret;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Creates a new oAuth2 client')
            ->addOption(
                'redirect-uri',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Sets redirect uri for client. Use this option multiple times to set multiple redirect URIs.',
                []
            )
            ->addOption(
                'grant-type',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Sets allowed grant type for client. Use this option multiple times to set multiple grant types.',
                []
            )
            ->addOption(
                'scope',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Sets allowed scope for client. Use this option multiple times to set multiple scopes.',
                []
            )
            ->addArgument(
                'identifier',
                InputArgument::OPTIONAL,
                'The client identifier'
            )
            ->addArgument(
                'secret',
                InputArgument::OPTIONAL,
                'The client secret'
            )
            ->addOption(
                'public',
                null,
                InputOption::VALUE_NONE,
                'Create a public client.'
            )
            ->addOption(
                'allow-plain-text-pkce',
                null,
                InputOption::VALUE_NONE,
                'Create a client who is allowed to use plain challenge method for PKCE.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $clientData = $this->buildClientFromInput($input);
        } catch (InvalidArgumentException $exception) {
            $io->error($exception->getMessage());

            return 1;
        }

        /** @var Client $client */
        $client = $clientData['client'];
        $this->clientManager->save($client);
        $io->success('New oAuth2 client created successfully.');

        $headers = ['Identifier', 'Secret'];
        $rows = [
            [$client->getIdentifier(), $clientData['plain_secret']],
        ];
        $io->table($headers, $rows);

        return 0;
    }

    private function buildClientFromInput(InputInterface $input): array
    {
        $identifier = $input->getArgument('identifier') ?? hash('md5', random_bytes(16));

        $isPublic = $input->getOption('public');

        if (null !== $input->getArgument('secret') && $isPublic) {
            throw new InvalidArgumentException('The client cannot have a secret and be public.');
        }

        $secret = $isPublic ? null : $input->getArgument('secret') ?? hash('sha512', random_bytes(32));

        $cryptSecret = null;
        if ($secret !== null) {
            $cryptSecret = $this->cryptClientSecret ? password_hash($secret, PASSWORD_DEFAULT) : $secret;
        }

        $client = new Client($identifier, $cryptSecret);
        $client->setActive(true);
        $client->setAllowPlainTextPkce($input->getOption('allow-plain-text-pkce'));

        $redirectUris = array_map(
            static function (string $redirectUri): RedirectUri { return new RedirectUri($redirectUri); },
            $input->getOption('redirect-uri')
        );
        $client->setRedirectUris(...$redirectUris);

        $grants = array_map(
            static function (string $grant): Grant { return new Grant($grant); },
            $input->getOption('grant-type')
        );
        $client->setGrants(...$grants);

        $scopes = array_map(
            static function (string $scope): Scope { return new Scope($scope); },
            $input->getOption('scope')
        );
        $client->setScopes(...$scopes);

        return ['client' => $client, 'plain_secret' => $secret];
    }
}
