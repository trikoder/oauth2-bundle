<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\Doctrine\ClientManager as DoctrineClientManager;
use Trikoder\Bundle\OAuth2Bundle\Model\AccessToken;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;
use Trikoder\Bundle\OAuth2Bundle\Model\RefreshToken;

class DoctrineClientManagerTest extends AbstractAcceptanceTest
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
                ->findOneBy(
                    [
                        'identifier' => $client->getIdentifier(),
                    ]
                )
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

        $accessToken = new AccessToken('access token', (new DateTime())->modify('+1 day'), $client, $client->getIdentifier(), []);
        $em->persist($accessToken);
        $em->flush();

        $doctrineClientManager->remove($client);

        $this->assertNull(
            $em
                ->getRepository(Client::class)
                ->findOneBy(
                    [
                        'identifier' => $client->getIdentifier(),
                    ]
                )
        );

        $this->assertNull(
            $em
                ->getRepository(AccessToken::class)
                ->findOneBy(
                    [
                        'identifier' => $accessToken->getIdentifier(),
                    ]
                )
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

        $accessToken = new AccessToken('access token', (new DateTime())->modify('+1 day'), $client, $client->getIdentifier(), []);
        $em->persist($accessToken);
        $em->flush();

        $refreshToken = new RefreshToken('refresh token', (new DateTime())->modify('+1 day'), $accessToken);
        $em->persist($refreshToken);
        $em->flush();

        $doctrineClientManager->remove($client);

        $this->assertNull(
            $em
                ->getRepository(Client::class)
                ->findOneBy(
                    [
                        'identifier' => $client->getIdentifier(),
                    ]
                )
        );

        $this->assertNull(
            $em
                ->getRepository(AccessToken::class)
                ->findOneBy(
                    [
                        'identifier' => $accessToken->getIdentifier(),
                    ]
                )
        );

        $em->clear();

        /** @var $refreshToken RefreshToken */
        $refreshToken = $em
            ->getRepository(RefreshToken::class)
            ->findOneBy(
                [
                    'identifier' => $refreshToken->getIdentifier(),
                ]
            )
        ;
        $this->assertNotNull($refreshToken);
        $this->assertNull($refreshToken->getAccessToken());
    }
}
