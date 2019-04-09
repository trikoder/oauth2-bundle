<?php

namespace Trikoder\Bundle\OAuth2Bundle\Tests;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Trikoder\Bundle\OAuth2Bundle\Manager\AccessTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\AuthorizationCodeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\RefreshTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ScopeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Tests\Fixtures\FixtureFactory;
use Trikoder\Bundle\OAuth2Bundle\Tests\Fixtures\SecurityTestController;

class TestKernel extends Kernel implements CompilerPassInterface
{
    use MicroKernelTrait;

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        putenv(sprintf('PRIVATE_KEY_PATH=%s', TestHelper::PRIVATE_KEY_PATH));
        putenv(sprintf('PUBLIC_KEY_PATH=%s', TestHelper::PUBLIC_KEY_PATH));
        putenv(sprintf('ENCRYPTION_KEY=%s', TestHelper::ENCRYPTION_KEY));

        parent::boot();
    }

    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        return [
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Trikoder\Bundle\OAuth2Bundle\TrikoderOAuth2Bundle(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routes->import('@TrikoderOAuth2Bundle/Resources/config/routes.xml');

        $routes->add('/security-test', 'Trikoder\Bundle\OAuth2Bundle\Tests\Fixtures\SecurityTestController:helloAction');

        $routes
            ->add('/security-test-scopes', 'Trikoder\Bundle\OAuth2Bundle\Tests\Fixtures\SecurityTestController:scopeAction')
            ->setDefault('oauth2_scopes', ['fancy']);

        $routes
            ->add('/security-test-roles', 'Trikoder\Bundle\OAuth2Bundle\Tests\Fixtures\SecurityTestController:rolesAction')
            ->setDefault('oauth2_scopes', ['fancy']);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $container->loadFromExtension('doctrine', [
            'dbal' => [
                'driver' => 'sqlite',
                'charset' => 'utf8mb4',
                'url' => 'sqlite:///:memory:',
                'default_table_options' => [
                    'charset' => 'utf8mb4',
                    'utf8mb4_unicode_ci' => 'utf8mb4_unicode_ci',
                ],
            ],
            'orm' => null,
        ]);

        $container->loadFromExtension('framework', [
            'secret' => 'nope',
            'test' => null,
        ]);

        $container->loadFromExtension('security', [
            'firewalls' => [
                'test' => [
                    'pattern' => '^/security-test',
                    'stateless' => true,
                    'oauth2' => true,
                ],
            ],
            'providers' => [
                'in_memory' => [
                    'memory' => [
                        'users' => [
                            FixtureFactory::FIXTURE_USER => [
                                'roles' => ['ROLE_USER'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $container->loadFromExtension('sensio_framework_extra', [
            'router' => [
                'annotations' => false,
            ],
        ]);

        $container->loadFromExtension('trikoder_oauth2', [
            'authorization_server' => [
                'private_key' => '%env(PRIVATE_KEY_PATH)%',
                'encryption_key' => '%env(ENCRYPTION_KEY)%',
            ],
            'resource_server' => [
                'public_key' => '%env(PUBLIC_KEY_PATH)%',
            ],
            'scopes' => [
                FixtureFactory::FIXTURE_SCOPE_SECOND,
            ],
            'persistence' => [
                'doctrine' => [
                    'entity_manager' => 'default',
                ],
            ],
        ]);

        $container
            ->register(SecurityTestController::class)
            ->setAutoconfigured(true)
            ->setAutowired(true)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir()
    {
        return sprintf('%s/Tests/.kernel/%s/cache', $this->getProjectDir(), $this->getEnvironment());
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir()
    {
        return sprintf('%s/Tests/.kernel/%s/logs', $this->getProjectDir(), $this->getEnvironment());
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $container
            ->getDefinition(
                $container
                    ->getAlias(ScopeManagerInterface::class)
                    ->setPublic(true)
            )
            ->setPublic(true)
        ;

        $container
            ->getDefinition(
                $container
                    ->getAlias(ClientManagerInterface::class)
                    ->setPublic(true)
            )
            ->setPublic(true)
        ;

        $container
            ->getDefinition(
                $container
                    ->getAlias(AccessTokenManagerInterface::class)
                    ->setPublic(true)
            )
            ->setPublic(true)
        ;

        $container
            ->getDefinition(
                $container
                    ->getAlias(RefreshTokenManagerInterface::class)
                    ->setPublic(true)
            )
            ->setPublic(true)
        ;

        $container
            ->getDefinition(
                $container
                    ->getAlias(AuthorizationCodeManagerInterface::class)
                    ->setPublic(true)
            )
            ->setPublic(true)
        ;
    }
}
