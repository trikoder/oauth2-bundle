<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Unit;

use League\OAuth2\Server\AuthorizationServer;
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
                //Pick one for valid config:
                //'persistence' => ['doctrine' => []]
                'persistence' => ['in_memory' => 1],
            ],
        ];
    }

    private function setupContainer(ContainerBuilder $container): void
    {
        $container->register(ScopeManager::class);
        //the line below is no-longer needed if "persistence" above
        //is set to either doctrine or in_memory
        //which will ensure that services defined in either
        // storage/in_memory.xml or storage/doctrine.xml
        //get properly defined
        //$container->setAlias(ScopeManagerInterface::class, ScopeManager::class);
    }
}
