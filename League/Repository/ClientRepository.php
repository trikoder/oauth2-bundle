<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\League\Repository;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use Trikoder\Bundle\OAuth2Bundle\League\Entity\Client as ClientEntity;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\Client as ClientModel;

final class ClientRepository implements ClientRepositoryInterface
{
    /**
     * @var ClientManagerInterface
     */
    private $clientManager;

    public function __construct(ClientManagerInterface $clientManager)
    {
        $this->clientManager = $clientManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientEntity(
        $clientIdentifier,
        $grantType = null,
        $clientSecret = null,
        $mustValidateSecret = true
    ) {
        $client = $this->clientManager->find($clientIdentifier);

        if (null === $client) {
            return null;
        }

        if (!$client->isActive()) {
            return null;
        }

        if (!$this->isGrantSupported($client, $grantType)) {
            return null;
        }

        if (!$mustValidateSecret) {
            return $this->buildClientEntity($client);
        }

        if (!hash_equals($client->getSecret(), (string) $clientSecret)) {
            return null;
        }

        return $this->buildClientEntity($client);
    }

    private function buildClientEntity(ClientModel $client): ClientEntity
    {
        $clientEntity = new ClientEntity();
        $clientEntity->setIdentifier($client->getIdentifier());
        $clientEntity->setRedirectUri(array_map('strval', $client->getRedirectUris()));

        return $clientEntity;
    }

    private function isGrantSupported(ClientModel $client, ?string $grant): bool
    {
        if (null === $grant) {
            return true;
        }

        $grants = $client->getGrants();

        if (empty($grants)) {
            return true;
        }

        return \in_array($grant, $client->getGrants());
    }
}
