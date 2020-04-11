<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Unit;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Trikoder\Bundle\OAuth2Bundle\DependencyInjection\TrikoderOAuth2Extension;
use Trikoder\Bundle\OAuth2Bundle\Manager\InMemory\ScopeManager;

final class ExtensionTest extends TestCase
{
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
        yield 'Client credentials grant can be enabled' => [
            ClientCredentialsGrant::class, 'enable_client_credentials_grant', true,
        ];
        yield 'Client credentials grant can be disabled' => [
            ClientCredentialsGrant::class, 'enable_client_credentials_grant', false,
        ];
        yield 'Password grant can be enabled' => [
            PasswordGrant::class, 'enable_password_grant', true,
        ];
        yield 'Password grant can be disabled' => [
            PasswordGrant::class, 'enable_password_grant', false,
        ];
        yield 'Refresh token grant can be enabled' => [
            RefreshTokenGrant::class, 'enable_refresh_token_grant', true,
        ];
        yield 'Refresh token grant can be disabled' => [
            RefreshTokenGrant::class, 'enable_refresh_token_grant', false,
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

        $configuration = $this->getValidConfiguration();
        $configuration[0]['authorization_server']['require_code_challenge_for_public_clients'] = $requireCodeChallengeForPublicClients;

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
        yield 'when not requiring code challenge for public clients the requirement should be disabled' => [
            false, true,
        ];
        yield 'when code challenge for public clients is required the requirement should not be disabled' => [
            true, false,
        ];
        yield 'with the default value the requirement should not be disabled' => [
            null, false,
        ];
    }

    private function getValidConfiguration(array $options = []): array
    {
        return [
            [
                'authorization_server' => [
                    'private_key' => 'foo',
                    'encryption_key' => 'foo',
                    'enable_client_credentials_grant' => $options['enable_client_credentials_grant'] ?? true,
                    'enable_password_grant' => $options['enable_password_grant'] ?? true,
                    'enable_refresh_token_grant' => $options['enable_refresh_token_grant'] ?? true,
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
