<?php

namespace Trikoder\Bundle\OAuth2Bundle\Manager;

use Trikoder\Bundle\OAuth2Bundle\Model\Client;

interface ClientManagerInterface
{
    /**
     * @return Client[]
     */
    public function list(): array;

    public function find(string $identifier): ?Client;

    public function save(Client $client): void;

    public function remove(Client $client): void;
}
