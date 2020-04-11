<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Manager;

use Trikoder\Bundle\OAuth2Bundle\Model\Client;
use Trikoder\Bundle\OAuth2Bundle\Service\ClientFinderInterface;

interface ClientManagerInterface extends ClientFinderInterface
{
    public function save(Client $client): void;

    public function remove(Client $client): void;

    /**
     * @return Client[]
     */
    public function list(?ClientFilter $clientFilter): array;
}
