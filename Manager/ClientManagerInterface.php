<?php

namespace Trikoder\Bundle\OAuth2Bundle\Manager;

use Trikoder\Bundle\OAuth2Bundle\Model\Client;

interface ClientManagerInterface
{
    public function find(string $identifier): ?Client;

    public function save(Client $client): void;
}
