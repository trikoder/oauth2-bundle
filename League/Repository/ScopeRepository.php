<?php

namespace Trikoder\Bundle\OAuth2Bundle\League\Repository;

use League\OAuth2\Server\Entities\ClientEntityInterface;
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

    public function __construct(
        ScopeManagerInterface $scopeManager,
        ClientManagerInterface $clientManager,
        ScopeConverter $scopeConverter,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->scopeManager = $scopeManager;
        $this->clientManager = $clientManager;
        $this->scopeConverter = $scopeConverter;
        $this->eventDispatcher = $eventDispatcher;
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

        $event = $this->eventDispatcher->dispatch(
            OAuth2Events::SCOPE_RESOLVE,
            new ScopeResolveEvent(
                $this->scopeConverter->toDomainArray($scopes),
                new GrantModel($grantType),
                $client,
                $userIdentifier
            )
        );

        return $this->scopeConverter->toLeagueArray($event->getScopes());
    }
}
