<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\DependencyInjection;

use DateInterval;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use League\OAuth2\Server\CryptKey;
use LogicException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
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
use Trikoder\Bundle\OAuth2Bundle\DBAL\Type\Grant as GrantType;
use Trikoder\Bundle\OAuth2Bundle\DBAL\Type\RedirectUri as RedirectUriType;
use Trikoder\Bundle\OAuth2Bundle\DBAL\Type\Scope as ScopeType;
use Trikoder\Bundle\OAuth2Bundle\Manager\ScopeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\Scope as ScopeModel;

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
        $this->assertPsrHttpAliasesExist($container);
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
                throw new LogicException(
                    sprintf(
                        'Bundle \'%s\' needs to be enabled in your application kernel.',
                        $requiredBundle
                    )
                );
            }
        }
    }

    private function assertPsrHttpAliasesExist(ContainerBuilder $container): void
    {
        $requiredAliases = [
            ServerRequestFactoryInterface::class,
            StreamFactoryInterface::class,
            UploadedFileFactoryInterface::class,
            ResponseFactoryInterface::class,
        ];

        foreach ($requiredAliases as $requiredAlias) {
            $definition = $container
                ->getDefinition(
                    $container->getAlias($requiredAlias)
                )
            ;

            $aliasedClass = $definition->getClass();

            if (!class_exists($aliasedClass)) {
                throw new LogicException(
                    sprintf(
                        'Alias \'%s\' points to a non-existing class \'%s\'. Did you configure a PSR-7/17 compatible library?',
                        $requiredAlias,
                        $aliasedClass
                    )
                );
            }
        }
    }

    private function configureAuthorizationServer(ContainerBuilder $container, array $config): void
    {
        $authorizationServer = $container
            ->getDefinition('league.oauth2.server.authorization_server')
            ->replaceArgument('$privateKey', new Definition(CryptKey::class, [
                $config['private_key'],
                $config['private_key_passphrase'],
                false,
            ]))
            ->replaceArgument('$encryptionKey', $config['encryption_key'])
        ;

        if ($config['enable_client_credentials_grant']) {
            $authorizationServer->addMethodCall('enableGrantType', [
                new Reference('league.oauth2.server.grant.client_credentials_grant'),
                new Definition(DateInterval::class, [$config['access_token_ttl']]),
            ]);
        }

        if ($config['enable_password_grant']) {
            $authorizationServer->addMethodCall('enableGrantType', [
                new Reference('league.oauth2.server.grant.password_grant'),
                new Definition(DateInterval::class, [$config['access_token_ttl']]),
            ]);
        }

        if ($config['enable_refresh_token_grant']) {
            $authorizationServer->addMethodCall('enableGrantType', [
                new Reference('league.oauth2.server.grant.refresh_token_grant'),
                new Definition(DateInterval::class, [$config['access_token_ttl']]),
            ]);
        }

        $this->configureGrants($container, $config);
    }

    private function configureGrants(ContainerBuilder $container, array $config): void
    {
        $container
            ->getDefinition('league.oauth2.server.grant.password_grant')
            ->addMethodCall('setRefreshTokenTTL', [
                new Definition(DateInterval::class, [$config['refresh_token_ttl']]),
            ])
        ;

        $container
            ->getDefinition('league.oauth2.server.grant.refresh_token_grant')
            ->addMethodCall('setRefreshTokenTTL', [
                new Definition(DateInterval::class, [$config['refresh_token_ttl']]),
            ])
        ;
    }

    private function configurePersistence(LoaderInterface $loader, ContainerBuilder $container, array $config)
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
            ->getDefinition('trikoder.oauth2.manager.doctrine.access_token_manager')
            ->replaceArgument('$entityManager', $entityManager)
        ;

        $container
            ->getDefinition('trikoder.oauth2.manager.doctrine.client_manager')
            ->replaceArgument('$entityManager', $entityManager)
        ;

        $container
            ->getDefinition('trikoder.oauth2.manager.doctrine.refresh_token_manager')
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
            ->getDefinition('league.oauth2.server.resource_server')
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
