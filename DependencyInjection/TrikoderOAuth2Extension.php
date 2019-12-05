<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\DependencyInjection;

use DateInterval;
use Defuse\Crypto\Key;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\ImplicitGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\ResourceServer;
use LogicException;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
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
use Symfony\Component\HttpKernel\KernelEvents;
use Trikoder\Bundle\OAuth2Bundle\DBAL\Type\Grant as GrantType;
use Trikoder\Bundle\OAuth2Bundle\DBAL\Type\RedirectUri as RedirectUriType;
use Trikoder\Bundle\OAuth2Bundle\DBAL\Type\Scope as ScopeType;
use Trikoder\Bundle\OAuth2Bundle\EventListener\ConvertExceptionToResponseListener;
use Trikoder\Bundle\OAuth2Bundle\Manager\Doctrine\AccessTokenManager;
use Trikoder\Bundle\OAuth2Bundle\Manager\Doctrine\AuthorizationCodeManager;
use Trikoder\Bundle\OAuth2Bundle\Manager\Doctrine\ClientManager;
use Trikoder\Bundle\OAuth2Bundle\Manager\Doctrine\RefreshTokenManager;
use Trikoder\Bundle\OAuth2Bundle\Manager\ScopeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\Scope as ScopeModel;
use Trikoder\Bundle\OAuth2Bundle\Security\Authentication\Token\OAuth2TokenFactory;

final class TrikoderOAuth2Extension extends Extension implements PrependExtensionInterface, CompilerPassInterface
{
    /**
     * {@inheritdoc}
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

        $container->getDefinition(ConvertExceptionToResponseListener::class)
            ->addTag('kernel.event_listener', [
                'event' => KernelEvents::EXCEPTION,
                'method' => 'onKernelException',
                'priority' => $config['exception_event_listener_priority'],
            ]);
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
            'sensio_framework_extra' => SensioFrameworkExtraBundle::class,
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
                throw new \RuntimeException('You must install the "defuse/php-encryption" package to use "encryption_key_type: defuse".');
            }

            $keyDefinition = (new Definition(Key::class))
                ->setFactory([Key::class, 'loadFromAsciiSafeString'])
                ->addArgument($config['encryption_key']);
            $container->setDefinition('trikoder.oauth2.defuse_key', $keyDefinition);

            $authorizationServer->replaceArgument('$encryptionKey', new Reference('trikoder.oauth2.defuse_key'));
        }

        if ($config['enable_client_credentials_grant']) {
            $authorizationServer->addMethodCall('enableGrantType', [
                new Reference(ClientCredentialsGrant::class),
                new Definition(DateInterval::class, [$config['access_token_ttl']]),
            ]);
        }

        if ($config['enable_password_grant']) {
            $authorizationServer->addMethodCall('enableGrantType', [
                new Reference(PasswordGrant::class),
                new Definition(DateInterval::class, [$config['access_token_ttl']]),
            ]);
        }

        if ($config['enable_refresh_token_grant']) {
            $authorizationServer->addMethodCall('enableGrantType', [
                new Reference(RefreshTokenGrant::class),
                new Definition(DateInterval::class, [$config['access_token_ttl']]),
            ]);
        }

        if ($config['enable_auth_code_grant']) {
            $authorizationServer->addMethodCall('enableGrantType', [
                new Reference(AuthCodeGrant::class),
                new Definition(DateInterval::class, [$config['access_token_ttl']]),
            ]);
        }

        if ($config['enable_implicit_grant']) {
            $authorizationServer->addMethodCall('enableGrantType', [
                new Reference(ImplicitGrant::class),
                new Definition(DateInterval::class, [$config['access_token_ttl']]),
            ]);
        }

        $this->configureGrants($container, $config);
    }

    private function configureGrants(ContainerBuilder $container, array $config): void
    {
        $container
            ->getDefinition(PasswordGrant::class)
            ->addMethodCall('setRefreshTokenTTL', [
                new Definition(DateInterval::class, [$config['refresh_token_ttl']]),
            ])
        ;

        $container
            ->getDefinition(RefreshTokenGrant::class)
            ->addMethodCall('setRefreshTokenTTL', [
                new Definition(DateInterval::class, [$config['refresh_token_ttl']]),
            ])
        ;

        $container
            ->getDefinition(AuthCodeGrant::class)
            ->replaceArgument('$authCodeTTL', new Definition(DateInterval::class, [$config['auth_code_ttl']]))
            ->addMethodCall('setRefreshTokenTTL', [
                new Definition(DateInterval::class, [$config['refresh_token_ttl']]),
            ])
        ;

        $container
            ->getDefinition(ImplicitGrant::class)
            ->replaceArgument('$accessTokenTTL', new Definition(DateInterval::class, [$config['access_token_ttl']]))
        ;
    }

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
                $container->getAlias(ScopeManagerInterface::class)
            )
        ;

        foreach ($scopes as $scope) {
            $scopeManager->addMethodCall('save', [
                new Definition(ScopeModel::class, [$scope]),
            ]);
        }
    }
}
