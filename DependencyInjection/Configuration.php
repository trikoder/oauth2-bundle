<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\DependencyInjection;

use Defuse\Crypto\Key;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Trikoder\Bundle\OAuth2Bundle\OAuth2Grants;

final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('trikoder_oauth2');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode->append($this->createAuthorizationServerNode());
        $rootNode->append($this->createResourceServerNode());
        $rootNode->append($this->createScopesNode());
        $rootNode->append($this->createPersistenceNode());

        $rootNode
            ->children()
                ->scalarNode('exception_event_listener_priority')
                    ->info('The priority of the event listener that converts an Exception to a Response.')
                    ->defaultValue(10)
                ->end()
                ->scalarNode('role_prefix')
                    ->info('Set a custom prefix that replaces the default "ROLE_OAUTH2_" role prefix.')
                    ->defaultValue('ROLE_OAUTH2_')
                    ->cannotBeEmpty()
                ->end()
            ->end();

        return $treeBuilder;
    }

    private function createAuthorizationServerNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('authorization_server');
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
                    ->info('Passphrase of the private key, if any.')
                    ->defaultValue(null)
                ->end()
                ->scalarNode('encryption_key')
                    ->info(sprintf("The plain string or the ascii safe string used to create a %s to be used as an encryption key.\nHow to generate an encryption key: https://oauth2.thephpleague.com/installation/#string-password", Key::class))
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->enumNode('encryption_key_type')
                    ->info('The type of value of "encryption_key".')
                    ->values(['plain', 'defuse'])
                    ->defaultValue('plain')
                ->end()
                ->scalarNode('access_token_ttl')
                    ->info("How long the issued access token should be valid for, used as a default if there is no grant type specific value set.\nThe value should be a valid interval: http://php.net/manual/en/dateinterval.construct.php#refsect1-dateinterval.construct-parameters")
                    ->cannotBeEmpty()
                    ->defaultValue('PT1H')
                ->end()
                ->scalarNode('refresh_token_ttl')
                    ->info("How long the issued refresh token should be valid for, used as a default if there is no grant type specific value set.\nThe value should be a valid interval: http://php.net/manual/en/dateinterval.construct.php#refsect1-dateinterval.construct-parameters")
                    ->cannotBeEmpty()
                    ->defaultValue('P1M')
                ->end()
            ->end()
        ;

        $node->append($this->createAuthorizationServerGrantTypesNode());

        $node
            ->validate()
                ->always(static function ($v): array {
                    $grantTypesWithRefreshToken = array_flip(OAuth2Grants::WITH_REFRESH_TOKEN);

                    foreach ($v['grant_types'] as $grantType => &$grantTypeConfig) {
                        $grantTypeConfig['access_token_ttl'] = $grantTypeConfig['access_token_ttl'] ?? $v['access_token_ttl'];

                        if (isset($grantTypesWithRefreshToken[$grantType])) {
                            $grantTypeConfig['refresh_token_ttl'] = $grantTypeConfig['refresh_token_ttl'] ?? $v['refresh_token_ttl'];
                        }
                    }

                    unset($v['access_token_ttl'], $v['refresh_token_ttl']);

                    return $v;
                })
            ->end()
        ;

        return $node;
    }

    private function createAuthorizationServerGrantTypesNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('grant_types');
        $node = $treeBuilder->getRootNode();

        $node
            ->info('Enable and configure grant types.')
            ->addDefaultsIfNotSet()
        ;

        foreach (OAuth2Grants::ALL as $grantType => $grantTypeName) {
            $node
                ->children()
                    ->arrayNode($grantType)
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->booleanNode('enable')
                                ->info(sprintf('Whether to enable the %s grant.', $grantTypeName))
                                ->defaultTrue()
                            ->end()
                            ->scalarNode('access_token_ttl')
                                ->info(sprintf('How long the issued access token should be valid for the %s grant.', $grantTypeName))
                                ->cannotBeEmpty()
                                ->beforeNormalization()
                                    ->ifNull()
                                    ->thenUnset()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ;
        }

        foreach (OAuth2Grants::WITH_REFRESH_TOKEN as $grantType) {
            $node
                ->find($grantType)
                    ->children()
                        ->scalarNode('refresh_token_ttl')
                            ->info(sprintf('How long the issued refresh token should be valid for the %s grant.', OAuth2Grants::ALL[$grantType]))
                            ->cannotBeEmpty()
                            ->beforeNormalization()
                                ->ifNull()
                                ->thenUnset()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ;
        }

        $node
            ->find('authorization_code')
                ->children()
                    ->scalarNode('auth_code_ttl')
                        ->info("How long the issued authorization code should be valid for.\nThe value should be a valid interval: http://php.net/manual/en/dateinterval.construct.php#refsect1-dateinterval.construct-parameters")
                        ->cannotBeEmpty()
                        ->defaultValue('PT10M')
                    ->end()
                    ->booleanNode('require_code_challenge_for_public_clients')
                        ->info('Whether to require code challenge for public clients for the authorization code grant.')
                        ->defaultTrue()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function createResourceServerNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('resource_server');
        $node = $treeBuilder->getRootNode();

        $node
            ->isRequired()
            ->children()
                ->scalarNode('public_key')
                    ->info("Full path to the public key file.\nHow to generate a public key: https://oauth2.thephpleague.com/installation/#generating-public-and-private-keys")
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
        $treeBuilder = new TreeBuilder('scopes');
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
        $treeBuilder = new TreeBuilder('persistence');
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
}
