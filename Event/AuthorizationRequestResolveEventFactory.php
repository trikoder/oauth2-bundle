<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Event;

use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use RuntimeException;
use Trikoder\Bundle\OAuth2Bundle\Converter\ScopeConverterInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;

class AuthorizationRequestResolveEventFactory
{
    /**
     * @var ScopeConverterInterface
     */
    private $scopeConverter;

    /**
     * @var ClientManagerInterface
     */
    private $clientManager;

    public function __construct(ScopeConverterInterface $scopeConverter, ClientManagerInterface $clientManager)
    {
        $this->scopeConverter = $scopeConverter;
        $this->clientManager = $clientManager;
    }

    public function fromAuthorizationRequest(AuthorizationRequest $authorizationRequest): AuthorizationRequestResolveEvent
    {
        $scopes = $this->scopeConverter->toDomainArray($authorizationRequest->getScopes());

        $client = $this->clientManager->find($authorizationRequest->getClient()->getIdentifier());

        if (null === $client) {
            throw new RuntimeException(sprintf('No client found for the given identifier \'%s\'.', $authorizationRequest->getClient()->getIdentifier()));
        }

        return new AuthorizationRequestResolveEvent($authorizationRequest, $scopes, $client);
    }
}
