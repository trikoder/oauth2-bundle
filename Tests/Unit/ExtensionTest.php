<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Trikoder\Bundle\OAuth2Bundle\DependencyInjection\TrikoderOAuth2Extension;
use Trikoder\Bundle\OAuth2Bundle\Manager\InMemory\ScopeManager;
use Trikoder\Bundle\OAuth2Bundle\Manager\ScopeManagerInterface;

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

        $authorizationServer = $container->getDefinition('league.oauth2.server.authorization_server');
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
                'league.oauth2.server.grant.client_credentials_grant', 'enable_client_credentials_grant', true,
            ];
        yield 'Client credentials grant can be disabled' => [
                'league.oauth2.server.grant.client_credentials_grant', 'enable_client_credentials_grant', false,
            ];
        yield 'Password grant can be enabled' => [
                'league.oauth2.server.grant.password_grant', 'enable_password_grant', true,
            ];
        yield 'Password grant can be disabled' => [
                'league.oauth2.server.grant.password_grant', 'enable_password_grant', false,
            ];
        yield 'Refresh token grant can be enabled' => [
                'league.oauth2.server.grant.refresh_token_grant', 'enable_refresh_token_grant', true,
            ];
        yield 'Refresh token grant can be disabled' => [
                'league.oauth2.server.grant.refresh_token_grant', 'enable_refresh_token_grant', false,
            ];
    }

    private function getValidConfiguration(array $options): array
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
                'persistence' => [],
            ],
        ];
    }

    private function setupContainer(ContainerBuilder $container): void
    {
        $container->register(ScopeManager::class);
        $container->setAlias(ScopeManagerInterface::class, ScopeManager::class);
    }
}
