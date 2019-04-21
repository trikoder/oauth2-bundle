<?php

namespace Trikoder\Bundle\OAuth2Bundle\Manager\InMemory;

use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;

final class ClientManager implements ClientManagerInterface
{
    /**
     * @var Client[]
     */
    private $clients = [];

    /**
     * {@inheritdoc}
     */
    public function list(): array
    {
        return $this->clients;
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $identifier): ?Client
    {
        return $this->clients[$identifier] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Client $client): void
    {
        $this->clients[$client->getIdentifier()] = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Client $client): void
    {
        unset($this->clients[$client->getIdentifier()]);
    }
}
