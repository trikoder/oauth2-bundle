<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use DateTime;
use Doctrine\Persistence\ObjectManager;
use Trikoder\Bundle\OAuth2Bundle\Manager\Doctrine\ClientManager as DoctrineClientManager;
use Trikoder\Bundle\OAuth2Bundle\Model\AccessToken;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;
use Trikoder\Bundle\OAuth2Bundle\Model\RefreshToken;

/**
 * @TODO   This should be in the Integration tests folder but the current tests infrastructure would need improvements first.
 * @covers \Trikoder\Bundle\OAuth2Bundle\Manager\Doctrine\ClientManager
 */
final class DoctrineClientManagerTest extends AbstractAcceptanceTest
{
    public function testSimpleDelete(): void
    {
        /** @var $objectManager ObjectManager */
        $objectManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $doctrineClientManager = new DoctrineClientManager($objectManager);

        $client = new Client('client', 'secret');
        $objectManager->persist($client);
        $objectManager->flush();

        $doctrineClientManager->remove($client);

        $this->assertNull(
            $objectManager
                ->getRepository(Client::class)
                ->find($client->getIdentifier())
        );
    }

    public function testClientDeleteCascadesToAccessTokens(): void
    {
        /** @var $objectManager ObjectManager */
        $objectManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $doctrineClientManager = new DoctrineClientManager($objectManager);

        $client = new Client('client', 'secret');
        $objectManager->persist($client);
        $objectManager->flush();

        $accessToken = new AccessToken('access token', (new DateTime())->modify('+1 day'), $client, $client->getIdentifier(), []);
        $objectManager->persist($accessToken);
        $objectManager->flush();

        $doctrineClientManager->remove($client);

        $this->assertNull(
            $objectManager
                ->getRepository(Client::class)
                ->find($client->getIdentifier())
        );

        // The entity manager has to be cleared manually
        // because it doesn't process deep integrity constraints
        $objectManager->clear();

        $this->assertNull(
            $objectManager
                ->getRepository(AccessToken::class)
                ->find($accessToken->getIdentifier())
        );
    }

    public function testClientDeleteCascadesToAccessTokensAndRefreshTokens(): void
    {
        /** @var $objectManager ObjectManager */
        $objectManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $doctrineClientManager = new DoctrineClientManager($objectManager);

        $client = new Client('client', 'secret');
        $objectManager->persist($client);
        $objectManager->flush();

        $accessToken = new AccessToken('access token', (new DateTime())->modify('+1 day'), $client, $client->getIdentifier(), []);
        $objectManager->persist($accessToken);
        $objectManager->flush();

        $refreshToken = new RefreshToken('refresh token', (new DateTime())->modify('+1 day'), $accessToken);
        $objectManager->persist($refreshToken);
        $objectManager->flush();

        $doctrineClientManager->remove($client);

        $this->assertNull(
            $objectManager
                ->getRepository(Client::class)
                ->find($client->getIdentifier())
        );

        // The entity manager has to be cleared manually
        // because it doesn't process deep integrity constraints
        $objectManager->clear();

        $this->assertNull(
            $objectManager
                ->getRepository(AccessToken::class)
                ->find($accessToken->getIdentifier())
        );

        /** @var $refreshToken RefreshToken */
        $refreshToken = $objectManager
            ->getRepository(RefreshToken::class)
            ->find($refreshToken->getIdentifier())
        ;
        $this->assertNotNull($refreshToken);
        $this->assertNull($refreshToken->getAccessToken());
    }
}
