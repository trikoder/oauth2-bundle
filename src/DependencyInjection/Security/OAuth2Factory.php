<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\DependencyInjection\Security;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Trikoder\Bundle\OAuth2Bundle\Security\Authentication\Provider\OAuth2Provider;
use Trikoder\Bundle\OAuth2Bundle\Security\EntryPoint\OAuth2EntryPoint;
use Trikoder\Bundle\OAuth2Bundle\Security\Firewall\OAuth2Listener;

final class OAuth2Factory implements SecurityFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.oauth2.' . $id;
        $container
            ->setDefinition($providerId, new ChildDefinition(OAuth2Provider::class))
            ->replaceArgument('$userProvider', new Reference($userProvider))
            ->replaceArgument('$providerKey', $id);

        $listenerId = 'security.authentication.listener.oauth2.' . $id;
        $container
            ->setDefinition($listenerId, new ChildDefinition(OAuth2Listener::class))
            ->replaceArgument('$providerKey', $id);

        return [$providerId, $listenerId, OAuth2EntryPoint::class];
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return 'pre_auth';
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'oauth2';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(NodeDefinition $node)
    {
        return;
    }
}
