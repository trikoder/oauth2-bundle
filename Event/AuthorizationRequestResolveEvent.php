<?php

namespace Trikoder\Bundle\OAuth2Bundle\Event;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\EventDispatcher\Event;
use Trikoder\Bundle\OAuth2Bundle\League\Entity\User;

/**
 * Class AuthorizationRequestResolveEvent

 * @package Trikoder\Bundle\OAuth2Bundle\Event
 */
final class AuthorizationRequestResolveEvent extends Event
{
    /**
     * @var AuthorizationRequest
     */
    private $authorizationRequest;

    /**
     * @var null|ResponseInterface
     */
    private $response;

    public function __construct(AuthorizationRequest $authorizationRequest)
    {
        $this->authorizationRequest = $authorizationRequest;
    }

    public function getResponse(): ?ResponseInterface
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

    public function hasResponse(): bool
    {
        return $this->response !== null;
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

    public function setUser(User $user): void
    {
        $this->authorizationRequest->setUser($user);
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

    public function approveAuthorization():void
    {
        $this->authorizationRequest->setAuthorizationApproved(true);
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
