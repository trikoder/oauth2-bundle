<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Manager;

use Trikoder\Bundle\OAuth2Bundle\Model\ClientInterface;
use Trikoder\Bundle\OAuth2Bundle\Service\ClientFinderInterface;

interface ClientManagerInterface extends ClientFinderInterface
{
    public function save(ClientInterface $client): void;

    public function remove(ClientInterface $client): void;

    /**
     * @return ClientInterface[]
     */
    public function list(?ClientFilter $clientFilter): array;
}
