<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\DependencyInjection;

use Defuse\Crypto\Key;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = $this->getWrappedTreeBuilder('trikoder_oauth2');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode->append($this->createAuthorizationServerNode());
        $rootNode->append($this->createResourceServerNode());
        $rootNode->append($this->createScopesNode());
        $rootNode->append($this->createPersistenceNode());

        $rootNode
            ->children()
                ->scalarNode('exception_event_listener_priority')
                    ->info('The priority of the event listener that converts an Exception to a Response')
                    ->defaultValue(10)
                ->end()
                ->scalarNode('role_prefix')
                    ->info('Set a custom prefix that replaces the default \'ROLE_OAUTH2_\' role prefix')
                    ->defaultValue('ROLE_OAUTH2_')
                    ->cannotBeEmpty()
                ->end()
            ->end();

        return $treeBuilder;
    }

    private function createAuthorizationServerNode(): NodeDefinition
    {
        $treeBuilder = $this->getWrappedTreeBuilder('authorization_server');
        $node = $treeBuilder->getRootNode();

        $node
            ->isRequired()
            ->children()
                ->scalarNode('private_key')
                    ->info("Full path to the private key file.\nHow to generate a private key: https://oauth2.thephpleague.com/installation/#generating-public-and-private-keys")
                    ->example('/var/oauth/private.key')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('private_key_passphrase')
                    ->info('Passphrase of the private key, if any')
                    ->defaultValue(null)
                ->end()
                ->scalarNode('encryption_key')
                    ->info(sprintf("The plain string or the ascii safe string used to create a %s to be used as an encryption key.\nHow to generate an encryption key: https://oauth2.thephpleague.com/installation/#string-password", Key::class))
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->enumNode('encryption_key_type')
                    ->info("The type of value of 'encryption_key'")
                    ->values(['plain', 'defuse'])
                    ->defaultValue('plain')
                ->end()
                ->scalarNode('access_token_ttl')
                    ->info("How long the issued access token should be valid for.\nThe value should be a valid interval: http://php.net/manual/en/dateinterval.construct.php#refsect1-dateinterval.construct-parameters")
                    ->cannotBeEmpty()
                    ->defaultValue('PT1H')
                ->end()
                ->scalarNode('refresh_token_ttl')
                    ->info("How long the issued refresh token should be valid for.\nThe value should be a valid interval: http://php.net/manual/en/dateinterval.construct.php#refsect1-dateinterval.construct-parameters")
                    ->cannotBeEmpty()
                    ->defaultValue('P1M')
                ->end()
                ->scalarNode('auth_code_ttl')
                    ->info("How long the issued auth code should be valid for.\nThe value should be a valid interval: http://php.net/manual/en/dateinterval.construct.php#refsect1-dateinterval.construct-parameters")
                    ->cannotBeEmpty()
                    ->defaultValue('PT10M')
                ->end()
                ->booleanNode('enable_client_credentials_grant')
                    ->info('Whether to enable the client credentials grant')
                    ->defaultTrue()
                ->end()
                ->booleanNode('enable_password_grant')
                    ->info('Whether to enable the password grant')
                    ->defaultTrue()
                ->end()
                ->booleanNode('enable_refresh_token_grant')
                    ->info('Whether to enable the refresh token grant')
                    ->defaultTrue()
                ->end()
                ->booleanNode('enable_auth_code_grant')
                    ->info('Whether to enable the authorization code grant')
                    ->defaultTrue()
                ->end()
                ->booleanNode('enable_implicit_grant')
                    ->info('Whether to enable the implicit grant')
                    ->defaultTrue()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function createResourceServerNode(): NodeDefinition
    {
        $treeBuilder = $this->getWrappedTreeBuilder('resource_server');
        $node = $treeBuilder->getRootNode();

        $node
            ->isRequired()
            ->children()
                ->scalarNode('public_key')
                    ->info("Full path to the public key file\nHow to generate a public key: https://oauth2.thephpleague.com/installation/#generating-public-and-private-keys")
                    ->example('/var/oauth/public.key')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function createScopesNode(): NodeDefinition
    {
        $treeBuilder = $this->getWrappedTreeBuilder('scopes');
        $node = $treeBuilder->getRootNode();

        $node
            ->info("Scopes that you wish to utilize in your application.\nThis should be a simple array of strings.")
            ->scalarPrototype()
            ->treatNullLike([])
        ;

        return $node;
    }

    private function createPersistenceNode(): NodeDefinition
    {
        $treeBuilder = $this->getWrappedTreeBuilder('persistence');
        $node = $treeBuilder->getRootNode();

        $node
            ->info("Configures different persistence methods that can be used by the bundle for saving client and token data.\nOnly one persistence method can be configured at a time.")
            ->isRequired()
            ->performNoDeepMerging()
            ->children()
                // Doctrine persistence
                ->arrayNode('doctrine')
                    ->children()
                        ->scalarNode('entity_manager')
                            ->info('Name of the entity manager that you wish to use for managing clients and tokens.')
                            ->cannotBeEmpty()
                            ->defaultValue('default')
                        ->end()
                    ->end()
                ->end()
                // In-memory persistence
                ->scalarNode('in_memory')
                ->end()
            ->end()
        ;

        return $node;
    }

    private function getWrappedTreeBuilder(string $name): TreeBuilder
    {
        return new class($name) extends TreeBuilder {
            public function __construct(string $name)
            {
                // Compatibility path for Symfony 3.4
                if (!method_exists(TreeBuilder::class, 'getRootNode')) {
                    $this->root($name);
                }

                // Compatibility path for Symfony 4.2+
                if (method_exists(TreeBuilder::class, '__construct')) {
                    parent::__construct($name);
                }
            }

            public function getRootNode(): NodeDefinition
            {
                return $this->root;
            }
        };
    }
}
