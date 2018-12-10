<?php

namespace Trikoder\Bundle\OAuth2Bundle\League\Repository;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trikoder\Bundle\OAuth2Bundle\Converter\ScopeConverter;
use Trikoder\Bundle\OAuth2Bundle\Event\ScopeResolveEvent;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ScopeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\Grant as GrantModel;
use Trikoder\Bundle\OAuth2Bundle\OAuth2Events;

final class ScopeRepository implements ScopeRepositoryInterface
{
    /**
     * @var ScopeManagerInterface
     */
    private $scopeManager;

    /**
     * @var ClientManagerInterface
     */
    private $clientManager;

    /**
     * @var ScopeConverter
     */
    private $scopeConverter;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var bool
     */
    private $strictScopes;

    public function __construct(
        ScopeManagerInterface $scopeManager,
        ClientManagerInterface $clientManager,
        ScopeConverter $scopeConverter,
        EventDispatcherInterface $eventDispatcher,
        bool $strictScopes
    ) {
        $this->scopeManager = $scopeManager;
        $this->clientManager = $clientManager;
        $this->scopeConverter = $scopeConverter;
        $this->eventDispatcher = $eventDispatcher;
        $this->strictScopes = $strictScopes;
    }

    /**
     * {@inheritdoc}
     */
    public function getScopeEntityByIdentifier($identifier)
    {
        $scope = $this->scopeManager->find($identifier);

        if (null === $scope) {
            return null;
        }

        return $this->scopeConverter->toLeague($scope);
    }

    /**
     * {@inheritdoc}
     */
    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    ) {
        $client = $this->clientManager->find($clientEntity->getIdentifier());
        $scopes = $this->scopeConverter->toDomainArray($scopes);

        if (!$this->strictScopes) {
            $scopes = $this->inheritScopes($scopes, $client->getScopes());
        }

        $this->validateScopes($scopes, $client->getScopes());

        $event = $this->eventDispatcher->dispatch(
            OAuth2Events::SCOPE_RESOLVE,
            new ScopeResolveEvent(
                $scopes,
                new GrantModel($grantType),
                $client,
                $userIdentifier
            )
        );

        return $this->scopeConverter->toLeagueArray($event->getScopes());
    }

    private function inheritScopes(array $requestedScopes, array $clientScopes): array
    {
        if (!empty($requestedScopes)) {
            return $requestedScopes;
        }

        if (!empty($clientScopes)) {
            return $clientScopes;
        }

        //fallback to scopes in configuration
        return $this->scopeManager->findAll();
    }

    private function validateScopes(array $requestedScopes, array $clientScopes): void
    {
        if (!empty($clientScopes)) {
            $this->validateScopesAgainstClientScopes($requestedScopes, $clientScopes);
        } else {
            $this->validateScopesAgainstConfigurationScopes($requestedScopes);
        }
    }

    private function validateScopesAgainstClientScopes(array $requestedScopes, array $clientScopes): void
    {
        if (empty($requestedScopes)) {
            throw OAuthServerException::invalidScope('');
        }

        foreach ($requestedScopes as $requestedScope) {
            if (!\in_array($requestedScope, $clientScopes)) {
                throw OAuthServerException::invalidScope($requestedScope);
            }
        }
    }

    private function validateScopesAgainstConfigurationScopes(array $requestedScopes): void
    {
        $configurationScopes = $this->scopeManager->findAll();

        if (empty($requestedScopes) && !empty($configurationScopes)) {
            throw OAuthServerException::invalidScope('');
        }

        foreach ($requestedScopes as $requestedScope) {
            if (!\in_array($requestedScope, $configurationScopes)) {
                throw OAuthServerException::invalidScope($requestedScope);
            }
        }
    }
}
