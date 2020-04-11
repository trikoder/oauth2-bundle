<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
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
        /** @var $em EntityManagerInterface */
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $doctrineClientManager = new DoctrineClientManager($em);

        $client = new Client('client', 'secret');
        $em->persist($client);
        $em->flush();

        $doctrineClientManager->remove($client);

        $this->assertNull(
            $em
                ->getRepository(Client::class)
                ->find($client->getIdentifier())
        );
    }

    public function testClientDeleteCascadesToAccessTokens(): void
    {
        /** @var $em EntityManagerInterface */
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $doctrineClientManager = new DoctrineClientManager($em);

        $client = new Client('client', 'secret');
        $em->persist($client);
        $em->flush();

        $accessToken = new AccessToken('access token', new DateTimeImmutable('+1 day'), $client, $client->getIdentifier(), []);
        $em->persist($accessToken);
        $em->flush();

        $doctrineClientManager->remove($client);

        $this->assertNull(
            $em
                ->getRepository(Client::class)
                ->find($client->getIdentifier())
        );

        // The entity manager has to be cleared manually
        // because it doesn't process deep integrity constraints
        $em->clear();

        $this->assertNull(
            $em
                ->getRepository(AccessToken::class)
                ->find($accessToken->getIdentifier())
        );
    }

    public function testClientDeleteCascadesToAccessTokensAndRefreshTokens(): void
    {
        /** @var $em EntityManagerInterface */
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $doctrineClientManager = new DoctrineClientManager($em);

        $client = new Client('client', 'secret');
        $em->persist($client);
        $em->flush();

        $accessToken = new AccessToken('access token', new DateTimeImmutable('+1 day'), $client, $client->getIdentifier(), []);
        $em->persist($accessToken);
        $em->flush();

        $refreshToken = new RefreshToken('refresh token', new DateTimeImmutable('+1 day'), $accessToken);
        $em->persist($refreshToken);
        $em->flush();

        $doctrineClientManager->remove($client);

        $this->assertNull(
            $em
                ->getRepository(Client::class)
                ->find($client->getIdentifier())
        );

        // The entity manager has to be cleared manually
        // because it doesn't process deep integrity constraints
        $em->clear();

        $this->assertNull(
            $em
                ->getRepository(AccessToken::class)
                ->find($accessToken->getIdentifier())
        );

        /** @var $refreshToken RefreshToken */
        $refreshToken = $em
            ->getRepository(RefreshToken::class)
            ->find($refreshToken->getIdentifier())
        ;
        $this->assertNotNull($refreshToken);
        $this->assertNull($refreshToken->getAccessToken());
    }
}
