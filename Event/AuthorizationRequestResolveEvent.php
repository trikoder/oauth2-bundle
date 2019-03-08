<?php

namespace Trikoder\Bundle\OAuth2Bundle\Event;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\EventDispatcher\Event;

final class AuthorizationRequestResolveEvent extends Event
{
    public const AUTHORIZATION_APPROVED = true;
    public const AUTHORIZATION_DENIED = false;

    /**
     * @var AuthorizationRequest
     */
    private $authorizationRequest;

    /**
     * @var ?ResponseInterface
     */
    private $response;

    /**
     * @var bool
     */
    private $authorizationResolution = self::AUTHORIZATION_DENIED;

    public function __construct(AuthorizationRequest $authorizationRequest)
    {
        $this->authorizationRequest = $authorizationRequest;
    }

    public function getAuhorizationResolution(): bool
    {
        return $this->authorizationResolution;
    }

    public function resolveAuthorization(bool $authorizationResolution): void
    {
        $this->authorizationResolution = $authorizationResolution;
        $this->response = null;
    }

    public function hasResponse(): bool
    {
        return $this->response instanceof ResponseInterface;
    }

    public function getResponse(): ResponseInterface
    {
        if (!$this->hasResponse()) {
            throw new LogicException('There is no response. You should call "hasResponse" to check if the response exists.');
        }

        return $this->response;
    }

    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }

    public function getGrantTypeId(): string
    {
        return $this->authorizationRequest->getGrantTypeId();
    }

    public function getClient(): ClientEntityInterface
    {
        return $this->authorizationRequest->getClient();
    }

    public function getUser(): UserEntityInterface
    {
        return $this->authorizationRequest->getUser();
    }

    /**
     * @return ScopeEntityInterface[]
     */
    public function getScopes(): array
    {
        return $this->authorizationRequest->getScopes();
    }

    public function isAuthorizationApproved(): bool
    {
        return $this->authorizationRequest->isAuthorizationApproved();
    }

    public function getRedirectUri(): ?string
    {
        return $this->authorizationRequest->getRedirectUri();
    }

    public function getState(): ?string
    {
        return $this->authorizationRequest->getState();
    }

    public function getCodeChallenge(): string
    {
        return $this->authorizationRequest->getCodeChallenge();
    }

    public function getCodeChallengeMethod(): string
    {
        return $this->authorizationRequest->getCodeChallengeMethod();
    }
}
