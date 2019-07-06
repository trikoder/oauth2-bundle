<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Event;

use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Trikoder\Bundle\OAuth2Bundle\Converter\ScopeConverter;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;

class AuthorizationRequestResolveEventFactory
{
    /**
     * @var ScopeConverter
     */
    private $scopeConverter;

    /**
     * @var ClientManagerInterface
     */
    private $clientManager;

    public function __construct(ScopeConverter $scopeConverter, ClientManagerInterface $clientManager)
    {
        $this->scopeConverter = $scopeConverter;
        $this->clientManager = $clientManager;
    }

    public function fromAuthorizationRequest(AuthorizationRequest $authorizationRequest): AuthorizationRequestResolveEventInterface
    {
        return new AuthorizationRequestResolveEvent($authorizationRequest, $this->scopeConverter, $this->clientManager);
    }
}
