<?php

declare(strict_types=1);

namespace Integration;

use Trikoder\Bundle\OAuth2Bundle\League\Repository\ClientRepository;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;
use Trikoder\Bundle\OAuth2Bundle\Tests\Integration\AbstractIntegrationTest;

/**
 * @author Florent Blaison
 */
final class ClientRepositoryTest extends AbstractIntegrationTest
{
    public function testValidateClientWithPlainSecret(): void
    {
        $identifier = 'foo';
        $secret = 'bar';

        $client = new Client($identifier, $secret);

        $this->clientManager->save($client);

        $this->assertSame($client, $this->clientManager->find($identifier));

        $clientRepository = new ClientRepository($this->clientManager, false);

        $this->assertTrue($clientRepository->validateClient($identifier, $secret, null));
    }

    public function testValidateClientWithCryptSecret(): void
    {
        $identifier = 'foo';
        $secret = 'bar';

        $client = new Client($identifier, password_hash($secret, PASSWORD_DEFAULT));

        $this->clientManager->save($client);

        $this->assertSame($client, $this->clientManager->find($identifier));

        $clientRepository = new ClientRepository($this->clientManager, true);

        $this->assertTrue($clientRepository->validateClient($identifier, $secret, null));
    }
}
