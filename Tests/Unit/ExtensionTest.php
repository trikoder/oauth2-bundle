<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Unit;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\ImplicitGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Trikoder\Bundle\OAuth2Bundle\DependencyInjection\TrikoderOAuth2Extension;
use Trikoder\Bundle\OAuth2Bundle\Manager\InMemory\ScopeManager;

final class ExtensionTest extends TestCase
{
    /**
     * @dataProvider accessTokenTTLProvider
     */
    public function testAccessTokenTTLAndRefreshTokenTTL(array $configTTLs, array $expectedTTLs): void
    {
        $container = new ContainerBuilder();

        $this->setupContainer($container);

        $extension = new TrikoderOAuth2Extension();

        $extension->load($this->getValidConfiguration($configTTLs), $container);

        $authorizationServer = $container->getDefinition(AuthorizationServer::class);
        $methodCalls = $authorizationServer->getMethodCalls();

        foreach ($methodCalls as $methodCall) {
            if ('enableGrantType' === $methodCall[0]) {
                $referenceId = (string) $methodCall[1][0];
                /** @var Definition $accessTokenTTL */
                $accessTokenTTL = $methodCall[1][1];

                $this->assertSame(
                    $expectedTTLs[$referenceId],
                    $accessTokenTTL->getArgument(0),
                    sprintf('Call enableGrantType with "%s".', $referenceId)
                );
            }
        }

        $implicitGrant = $container->getDefinition(ImplicitGrant::class);

        $this->assertSame(
            $expectedTTLs[ImplicitGrant::class],
            $implicitGrant->getArgument('$accessTokenTTL')->getArgument(0),
            sprintf('Pass argument to "%s".', ImplicitGrant::class)
        );
    }

    public function accessTokenTTLProvider(): iterable
    {
        yield 'Default access token TTL can be set' => [[
            'access_token_ttl' => 'PT3H',
        ], [
            AuthCodeGrant::class => 'PT3H',
            ClientCredentialsGrant::class => 'PT3H',
            ImplicitGrant::class => 'PT3H',
            PasswordGrant::class => 'PT3H',
            RefreshTokenGrant::class => 'PT3H',
        ]];

        yield 'Default & client credentials grant type access token can be set' => [[
            'access_token_ttl' => 'PT6H',
            'client_credentials.access_token_ttl' => 'PT4H',
        ], [
            AuthCodeGrant::class => 'PT6H',
            ClientCredentialsGrant::class => 'PT4H',
            ImplicitGrant::class => 'PT6H',
            PasswordGrant::class => 'PT6H',
            RefreshTokenGrant::class => 'PT6H',
        ]];

        yield 'Password grant type access token can be set' => [[
            'password.access_token_ttl' => 'PT5H',
        ], [
            AuthCodeGrant::class => 'PT1H',
            ClientCredentialsGrant::class => 'PT1H',
            ImplicitGrant::class => 'PT1H',
            PasswordGrant::class => 'PT5H',
            RefreshTokenGrant::class => 'PT1H',
        ]];

        yield 'Multiple per grant type access tokens can be set' => [[
            'access_token_ttl' => 'PT3H',
            'authorization_code.access_token_ttl' => 'PT7H',
            'implicit.access_token_ttl' => 'PT5H',
            'refresh_token.access_token_ttl' => 'PT9H',
        ], [
            AuthCodeGrant::class => 'PT7H',
            ClientCredentialsGrant::class => 'PT3H',
            ImplicitGrant::class => 'PT5H',
            PasswordGrant::class => 'PT3H',
            RefreshTokenGrant::class => 'PT9H',
        ]];
    }

    public function testExceptionIsThrownForEmptyGrantTypeAccessTokenTTL(): void
    {
        $container = new ContainerBuilder();

        $this->setupContainer($container);

        $extension = new TrikoderOAuth2Extension();

        $this->expectException(InvalidConfigurationException::class);

        $extension->load($this->getValidConfiguration([
            'password.access_token_ttl' => '',
        ]), $container);
    }

    /**
     * @dataProvider refreshTokenTTLProvider
     */
    public function testRefreshTokenTTLAndRefreshTokenTTL(array $configTTLs, array $expectedTTLs): void
    {
        $container = new ContainerBuilder();

        $this->setupContainer($container);

        $extension = new TrikoderOAuth2Extension();

        $extension->load($this->getValidConfiguration($configTTLs), $container);

        foreach ($expectedTTLs as $referenceId => $expectedTTL) {
            $grant = $container->getDefinition($referenceId);

            $methodCalls = $grant->getMethodCalls();

            foreach ($methodCalls as $methodCall) {
                if ('setRefreshTokenTTL' === $methodCall[0]) {
                    /** @var Definition $refreshTokenTTL */
                    $refreshTokenTTL = $methodCall[1][0];

                    $this->assertSame(
                        $expectedTTLs[$referenceId],
                        $refreshTokenTTL->getArgument(0),
                        sprintf('Call setRefreshTokenTTL with "%s".', $referenceId)
                    );
                }
            }
        }
    }

    public function refreshTokenTTLProvider(): iterable
    {
        yield 'Default refresh token TTL can be set' => [[
            'refresh_token_ttl' => 'P3M',
        ], [
            AuthCodeGrant::class => 'P3M',
            PasswordGrant::class => 'P3M',
            RefreshTokenGrant::class => 'P3M',
        ]];

        yield 'Default & authorization code grant type refresh token can be set' => [[
            'refresh_token_ttl' => 'P6M',
            'authorization_code.refresh_token_ttl' => 'P4M',
        ], [
            AuthCodeGrant::class => 'P4M',
            PasswordGrant::class => 'P6M',
            RefreshTokenGrant::class => 'P6M',
        ]];

        yield 'Password grant type refresh token can be set' => [[
            'password.refresh_token_ttl' => 'P5M',
        ], [
            AuthCodeGrant::class => 'P1M',
            PasswordGrant::class => 'P5M',
            RefreshTokenGrant::class => 'P1M',
        ]];

        yield 'Multiple per grant type refresh tokens can be set' => [[
            'refresh_token_ttl' => 'P3M',
            'authorization_code.refresh_token_ttl' => 'P7M',
            'refresh_token.refresh_token_ttl' => 'P9M',
        ], [
            AuthCodeGrant::class => 'P7M',
            PasswordGrant::class => 'P3M',
            RefreshTokenGrant::class => 'P9M',
        ]];
    }

    public function testExceptionIsThrownForEmptyGrantTypeRefreshTokenTTL(): void
    {
        $container = new ContainerBuilder();

        $this->setupContainer($container);

        $extension = new TrikoderOAuth2Extension();

        $this->expectException(InvalidConfigurationException::class);

        $extension->load($this->getValidConfiguration([
            'password.refresh_token_ttl' => '',
        ]), $container);
    }

    /**
     * @dataProvider grantsProvider
     */
    public function testEnablingAndDisablingGrants(string $referenceId, string $grantKey, bool $shouldTheGrantBeEnabled): void
    {
        $container = new ContainerBuilder();

        $this->setupContainer($container);

        $extension = new TrikoderOAuth2Extension();

        $extension->load($this->getValidConfiguration([$grantKey => $shouldTheGrantBeEnabled]), $container);

        $authorizationServer = $container->getDefinition(AuthorizationServer::class);
        $methodCalls = $authorizationServer->getMethodCalls();
        $isGrantEnabled = false;

        foreach ($methodCalls as $methodCall) {
            if ('enableGrantType' === $methodCall[0] && $referenceId === (string) $methodCall[1][0]) {
                $isGrantEnabled = true;
                break;
            }
        }

        $this->assertSame($shouldTheGrantBeEnabled, $isGrantEnabled);
    }

    public function grantsProvider(): iterable
    {
        yield 'Authorization code grant can be enabled' => [
            AuthCodeGrant::class, 'authorization_code.enable', true,
        ];
        yield 'Authorization code grant can be disabled' => [
            AuthCodeGrant::class, 'authorization_code.enable', false,
        ];
        yield 'Client credentials grant can be enabled' => [
            ClientCredentialsGrant::class, 'client_credentials.enable', true,
        ];
        yield 'Client credentials grant can be disabled' => [
            ClientCredentialsGrant::class, 'client_credentials.enable', false,
        ];
        yield 'Implicit grant can be enabled' => [
            ImplicitGrant::class, 'implicit.enable', true,
        ];
        yield 'Implicit grant can be disabled' => [
            ImplicitGrant::class, 'implicit.enable', false,
        ];
        yield 'Password grant can be enabled' => [
            PasswordGrant::class, 'password.enable', true,
        ];
        yield 'Password grant can be disabled' => [
            PasswordGrant::class, 'password.enable', false,
        ];
        yield 'Refresh token grant can be enabled' => [
            RefreshTokenGrant::class, 'refresh_token.enable', true,
        ];
        yield 'Refresh token grant can be disabled' => [
            RefreshTokenGrant::class, 'refresh_token.enable', false,
        ];
    }

    /**
     * @dataProvider requireCodeChallengeForPublicClientsProvider
     */
    public function testAuthCodeGrantDisableRequireCodeChallengeForPublicClientsConfig(
        ?bool $requireCodeChallengeForPublicClients,
        bool $shouldTheRequirementBeDisabled
    ): void {
        $container = new ContainerBuilder();

        $this->setupContainer($container);

        $extension = new TrikoderOAuth2Extension();

        $configuration = $this->getValidConfiguration([
            'authorization_code.require_code_challenge_for_public_clients' => $requireCodeChallengeForPublicClients,
        ]);

        $extension->load($configuration, $container);

        $authorizationServer = $container->getDefinition(AuthCodeGrant::class);
        $methodCalls = $authorizationServer->getMethodCalls();

        $isRequireCodeChallengeForPublicClientsDisabled = false;

        foreach ($methodCalls as $methodCall) {
            if ('disableRequireCodeChallengeForPublicClients' === $methodCall[0]) {
                $isRequireCodeChallengeForPublicClientsDisabled = true;
                break;
            }
        }

        $this->assertSame($shouldTheRequirementBeDisabled, $isRequireCodeChallengeForPublicClientsDisabled);
    }

    public function requireCodeChallengeForPublicClientsProvider(): iterable
    {
        yield 'When not requiring code challenge for public clients the requirement should be disabled' => [
            false, true,
        ];
        yield 'When code challenge for public clients is required the requirement should not be disabled' => [
            true, false,
        ];
        yield 'With the default value the requirement should not be disabled' => [
            null, false,
        ];
    }

    /**
     * @dataProvider authCodeTTLProvider
     */
    public function testAuthCodeTTLConfig(
        ?string $authCodeTTL,
        string $expectedAuthCodeTTL
    ): void {
        $container = new ContainerBuilder();

        $this->setupContainer($container);

        $extension = new TrikoderOAuth2Extension();

        $configuration = $this->getValidConfiguration([
            'authorization_code.auth_code_ttl' => $authCodeTTL,
        ]);

        $extension->load($configuration, $container);

        $authorizationServer = $container->getDefinition(AuthCodeGrant::class);

        $this->assertSame($expectedAuthCodeTTL, $authorizationServer->getArgument('$authCodeTTL')->getArgument(0));
    }

    public function authCodeTTLProvider(): iterable
    {
        yield 'Authorization code TTL can be set' => [
            'PT20M', 'PT20M',
        ];
        yield 'When no authorization code TTL is set, the default is used' => [
            null, 'PT10M',
        ];
    }

    private function getValidConfiguration(array $options = []): array
    {
        return [
            [
                'authorization_server' => [
                    'private_key' => 'foo',
                    'encryption_key' => 'foo',
                    'access_token_ttl' => $options['access_token_ttl'] ?? 'PT1H',
                    'refresh_token_ttl' => $options['refresh_token_ttl'] ?? 'P1M',
                    'grant_types' => [
                        'authorization_code' => [
                            'enable' => $options['authorization_code.enable'] ?? true,
                            'access_token_ttl' => $options['authorization_code.access_token_ttl'] ?? null,
                            'refresh_token_ttl' => $options['authorization_code.refresh_token_ttl'] ?? null,
                            'auth_code_ttl' => $options['authorization_code.auth_code_ttl'] ?? 'PT10M',
                            'require_code_challenge_for_public_clients' => $options['authorization_code.require_code_challenge_for_public_clients'] ?? true,
                        ],
                        'client_credentials' => [
                            'enable' => $options['client_credentials.enable'] ?? true,
                            'access_token_ttl' => $options['client_credentials.access_token_ttl'] ?? null,
                        ],
                        'implicit' => [
                            'enable' => $options['implicit.enable'] ?? true,
                            'access_token_ttl' => $options['implicit.access_token_ttl'] ?? null,
                        ],
                        'password' => [
                            'enable' => $options['password.enable'] ?? true,
                            'access_token_ttl' => $options['password.access_token_ttl'] ?? null,
                            'refresh_token_ttl' => $options['password.refresh_token_ttl'] ?? null,
                        ],
                        'refresh_token' => [
                            'enable' => $options['refresh_token.enable'] ?? true,
                            'access_token_ttl' => $options['refresh_token.access_token_ttl'] ?? null,
                            'refresh_token_ttl' => $options['refresh_token.refresh_token_ttl'] ?? null,
                        ],
                    ],
                ],
                'resource_server' => [
                    'public_key' => 'foo',
                ],
                //Pick one for valid config:
                //'persistence' => ['doctrine' => []]
                'persistence' => ['in_memory' => 1],
            ],
        ];
    }

    private function setupContainer(ContainerBuilder $container): void
    {
        $container->register(ScopeManager::class);
    }
}
