<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle;

use Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Doctrine\ODM\MongoDB\Mapping\MappingException;
use Doctrine\ODM\MongoDB\Types\Type;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Trikoder\Bundle\OAuth2Bundle\DBAL\Type\GrantOdm;
use Trikoder\Bundle\OAuth2Bundle\DBAL\Type\RedirectUriOdm;
use Trikoder\Bundle\OAuth2Bundle\DBAL\Type\ScopeOdm;
use Trikoder\Bundle\OAuth2Bundle\DependencyInjection\Security\OAuth2Factory;
use Trikoder\Bundle\OAuth2Bundle\DependencyInjection\TrikoderOAuth2Extension;

final class TrikoderOAuth2Bundle extends Bundle
{

    /**
     * {@inheritdoc}
     * @throws MappingException
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        if (class_exists(DoctrineMongoDBMappingsPass::class)) {
            Type::addType('oauth2_grant', GrantOdm::class);
            Type::addType('oauth2_redirect_uri', RedirectUriOdm::class);
            Type::addType('oauth2_scope', ScopeOdm::class);

            $this->configureDoctrineMongoExtension($container);
        } else {
            $this->configureDoctrineOrmExtension($container);
        }
        $this->configureSecurityExtension($container);
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new TrikoderOAuth2Extension();
    }

    private function configureSecurityExtension(ContainerBuilder $container): void
    {
        /** @var SecurityExtension $extension */
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new OAuth2Factory());
    }

    private function configureDoctrineOrmExtension(ContainerBuilder $container): void
    {
        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createXmlMappingDriver(
                [
                    realpath(__DIR__ . '/Resources/config/doctrine/model') => 'Trikoder\Bundle\OAuth2Bundle\Model',
                ],
                [
                    'trikoder.oauth2.persistence.doctrine.manager',
                ],
                'trikoder.oauth2.persistence.doctrine.enabled',
                [
                    'TrikoderOAuth2Bundle' => 'Trikoder\Bundle\OAuth2Bundle\Model',
                ]
            )
        );
    }

    private function configureDoctrineMongoExtension(ContainerBuilder $container): void
    {
        $container->addCompilerPass(
            DoctrineMongoDBMappingsPass::createXmlMappingDriver(
                [
                    realpath(__DIR__ . '/Resources/config/doctrine/model') => 'Trikoder\Bundle\OAuth2Bundle\Model',
                ],
                [
                    'trikoder.oauth2.persistence.doctrine.manager',
                ],
                'trikoder.oauth2.persistence.doctrine.enabled',
                [
                    'TrikoderOAuth2Bundle' => 'Trikoder\Bundle\OAuth2Bundle\Model',
                ]
            )
        );
    }
}
