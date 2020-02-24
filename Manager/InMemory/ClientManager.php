<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Manager\InMemory;

use Trikoder\Bundle\OAuth2Bundle\Manager\ClientFilter;
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

    /**
     * {@inheritdoc}
     */
    public function list(?ClientFilter $clientFilter): array
    {
        if (!$clientFilter || !$clientFilter->hasFilters()) {
            return $this->clients;
        }

        return array_filter($this->clients, static function (Client $client) use ($clientFilter): bool {
            $grantsPassed = self::passesFilter($client->getGrants(), $clientFilter->getGrants());
            $scopesPassed = self::passesFilter($client->getScopes(), $clientFilter->getScopes());
            $redirectUrisPassed = self::passesFilter($client->getRedirectUris(), $clientFilter->getRedirectUris());

            return $grantsPassed && $scopesPassed && $redirectUrisPassed;
        });
    }

    private static function passesFilter(array $clientValues, array $filterValues): bool
    {
        if (empty($filterValues)) {
            return true;
        }

        $clientValues = array_map('strval', $clientValues);
        $filterValues = array_map('strval', $filterValues);

        $valuesPassed = array_intersect($filterValues, $clientValues);

        return \count($valuesPassed) > 0;
    }
}
