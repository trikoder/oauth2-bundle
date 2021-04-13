<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\DependencyInjection;

use Ajgarlag\Bundle\PsrHttpMessageBundle\AjgarlagPsrHttpMessageBundle;
use DateInterval;
use Defuse\Crypto\Key;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Exception;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\ImplicitGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\ResourceServer;
use LogicException;
use RuntimeException;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Trikoder\Bundle\OAuth2Bundle\DBAL\Type\Grant as GrantType;
use Trikoder\Bundle\OAuth2Bundle\DBAL\Type\RedirectUri as RedirectUriType;
use Trikoder\Bundle\OAuth2Bundle\DBAL\Type\Scope as ScopeType;
use Trikoder\Bundle\OAuth2Bundle\League\AuthorizationServer\GrantTypeInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\Doctrine\AccessTokenManager;
use Trikoder\Bundle\OAuth2Bundle\Manager\Doctrine\AuthorizationCodeManager;
use Trikoder\Bundle\OAuth2Bundle\Manager\Doctrine\ClientManager;
use Trikoder\Bundle\OAuth2Bundle\Manager\Doctrine\RefreshTokenManager;
use Trikoder\Bundle\OAuth2Bundle\Manager\ScopeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\Scope as ScopeModel;
use Trikoder\Bundle\OAuth2Bundle\Security\Authentication\Token\OAuth2TokenFactory;
use Trikoder\Bundle\OAuth2Bundle\Service\CredentialsRevoker\DoctrineCredentialsRevoker;

final class TrikoderOAuth2Extension extends Extension implements PrependExtensionInterface, CompilerPassInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $config = $this->processConfiguration(new Configuration(), $configs);

        $this->configurePersistence($loader, $container, $config['persistence']);
        $this->configureAuthorizationServer($container, $config['authorization_server']);
        $this->configureResourceServer($container, $config['resource_server']);
        $this->configureScopes($container, $config['scopes']);

        $container->getDefinition(OAuth2TokenFactory::class)
            ->setArgument(0, $config['role_prefix']);

        $container->registerForAutoconfiguration(GrantTypeInterface::class)
            ->addTag('trikoder.oauth2.authorization_server.grant');
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'trikoder_oauth2';
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('doctrine', [
            'dbal' => [
                'connections' => null,
                'types' => [
                    'oauth2_grant' => GrantType::class,
                    'oauth2_redirect_uri' => RedirectUriType::class,
                    'oauth2_scope' => ScopeType::class,
                ],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->assertRequiredBundlesAreEnabled($container);
    }

    private function assertRequiredBundlesAreEnabled(ContainerBuilder $container): void
    {
        $requiredBundles = [
            'doctrine' => DoctrineBundle::class,
            'security' => SecurityBundle::class,
            'ajgarlag_psr_http_message' => AjgarlagPsrHttpMessageBundle::class,
        ];

        foreach ($requiredBundles as $bundleAlias => $requiredBundle) {
            if (!$container->hasExtension($bundleAlias)) {
                throw new LogicException(sprintf('Bundle \'%s\' needs to be enabled in your application kernel.', $requiredBundle));
            }
        }
    }

    private function configureAuthorizationServer(ContainerBuilder $container, array $config): void
    {
        $authorizationServer = $container
            ->getDefinition(AuthorizationServer::class)
            ->replaceArgument('$privateKey', new Definition(CryptKey::class, [
                $config['private_key'],
                $config['private_key_passphrase'],
                false,
            ]));

        if ('plain' === $config['encryption_key_type']) {
            $authorizationServer->replaceArgument('$encryptionKey', $config['encryption_key']);
        } elseif ('defuse' === $config['encryption_key_type']) {
            if (!class_exists(Key::class)) {
                throw new RuntimeException('You must install the "defuse/php-encryption" package to use "encryption_key_type: defuse".');
            }

            $keyDefinition = (new Definition(Key::class))
                ->setFactory([Key::class, 'loadFromAsciiSafeString'])
                ->addArgument($config['encryption_key']);
            $container->setDefinition('trikoder.oauth2.defuse_key', $keyDefinition);

            $authorizationServer->replaceArgument('$encryptionKey', new Reference('trikoder.oauth2.defuse_key'));
        }

        $grantTypes = $config['grant_types'];

        if ($grantTypes['client_credentials']['enable']) {
            $authorizationServer->addMethodCall('enableGrantType', [
                new Reference(ClientCredentialsGrant::class),
                new Definition(DateInterval::class, [$grantTypes['client_credentials']['access_token_ttl']]),
            ]);
        }

        if ($grantTypes['password']['enable']) {
            $authorizationServer->addMethodCall('enableGrantType', [
                new Reference(PasswordGrant::class),
                new Definition(DateInterval::class, [$grantTypes['password']['access_token_ttl']]),
            ]);
        }

        if ($grantTypes['refresh_token']['enable']) {
            $authorizationServer->addMethodCall('enableGrantType', [
                new Reference(RefreshTokenGrant::class),
                new Definition(DateInterval::class, [$grantTypes['refresh_token']['access_token_ttl']]),
            ]);
        }

        if ($grantTypes['authorization_code']['enable']) {
            $authorizationServer->addMethodCall('enableGrantType', [
                new Reference(AuthCodeGrant::class),
                new Definition(DateInterval::class, [$grantTypes['authorization_code']['access_token_ttl']]),
            ]);
        }

        if ($grantTypes['implicit']['enable']) {
            $authorizationServer->addMethodCall('enableGrantType', [
                new Reference(ImplicitGrant::class),
                new Definition(DateInterval::class, [$grantTypes['implicit']['access_token_ttl']]),
            ]);
        }

        $this->configureGrants($container, $config);
    }

    private function configureGrants(ContainerBuilder $container, array $config): void
    {
        $grantTypes = $config['grant_types'];

        $container
            ->getDefinition(PasswordGrant::class)
            ->addMethodCall('setRefreshTokenTTL', [
                new Definition(DateInterval::class, [$grantTypes['password']['refresh_token_ttl']]),
            ])
        ;

        $container
            ->getDefinition(RefreshTokenGrant::class)
            ->addMethodCall('setRefreshTokenTTL', [
                new Definition(DateInterval::class, [$grantTypes['refresh_token']['refresh_token_ttl']]),
            ])
        ;

        $authCodeGrantDefinition = $container->getDefinition(AuthCodeGrant::class);
        $authCodeGrantDefinition
            ->replaceArgument('$authCodeTTL', new Definition(DateInterval::class, [$grantTypes['authorization_code']['auth_code_ttl']]))
            ->addMethodCall('setRefreshTokenTTL', [
                new Definition(DateInterval::class, [$grantTypes['authorization_code']['refresh_token_ttl']]),
            ])
        ;

        if (false === $grantTypes['authorization_code']['require_code_challenge_for_public_clients']) {
            $authCodeGrantDefinition->addMethodCall('disableRequireCodeChallengeForPublicClients');
        }

        $container
            ->getDefinition(ImplicitGrant::class)
            ->replaceArgument('$accessTokenTTL', new Definition(DateInterval::class, [$grantTypes['implicit']['access_token_ttl']]))
        ;
    }

    /**
     * @throws Exception
     */
    private function configurePersistence(LoaderInterface $loader, ContainerBuilder $container, array $config): void
    {
        if (\count($config) > 1) {
            throw new LogicException('Only one persistence method can be configured at a time.');
        }

        $persistenceConfiguration = current($config);
        $persistenceMethod = key($config);

        switch ($persistenceMethod) {
            case 'in_memory':
                $loader->load('storage/in_memory.xml');
                $this->configureInMemoryPersistence($container);
                break;
            case 'doctrine':
                $loader->load('storage/doctrine.xml');
                $this->configureDoctrinePersistence($container, $persistenceConfiguration);
                break;
        }
    }

    private function configureDoctrinePersistence(ContainerBuilder $container, array $config): void
    {
        $entityManagerName = $config['entity_manager'];

        $entityManager = new Reference(
            sprintf('doctrine.orm.%s_entity_manager', $entityManagerName)
        );

        $container
            ->getDefinition(AccessTokenManager::class)
            ->replaceArgument('$entityManager', $entityManager)
        ;

        $container
            ->getDefinition(ClientManager::class)
            ->replaceArgument('$entityManager', $entityManager)
        ;

        $container
            ->getDefinition(RefreshTokenManager::class)
            ->replaceArgument('$entityManager', $entityManager)
        ;

        $container
            ->getDefinition(AuthorizationCodeManager::class)
            ->replaceArgument('$entityManager', $entityManager)
        ;

        $container
            ->getDefinition(DoctrineCredentialsRevoker::class)
            ->replaceArgument('$entityManager', $entityManager)
        ;

        $container->setParameter('trikoder.oauth2.persistence.doctrine.enabled', true);
        $container->setParameter('trikoder.oauth2.persistence.doctrine.manager', $entityManagerName);
    }

    private function configureInMemoryPersistence(ContainerBuilder $container): void
    {
        $container->setParameter('trikoder.oauth2.persistence.in_memory.enabled', true);
    }

    private function configureResourceServer(ContainerBuilder $container, array $config): void
    {
        $container
            ->getDefinition(ResourceServer::class)
            ->replaceArgument('$publicKey', new Definition(CryptKey::class, [
                $config['public_key'],
                null,
                false,
            ]))
        ;
    }

    private function configureScopes(ContainerBuilder $container, array $scopes): void
    {
        $scopeManager = $container
            ->getDefinition(
                (string) $container->getAlias(ScopeManagerInterface::class)
            )
        ;

        foreach ($scopes as $scope) {
            $scopeManager->addMethodCall('save', [
                new Definition(ScopeModel::class, [$scope]),
            ]);
        }
    }
}
