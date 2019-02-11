<?php

namespace Trikoder\Bundle\OAuth2Bundle\Event;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Security\Core\Exception\LogicException;

final class AuthorizationRequestResolveEvent extends Event
{
    public const AUTHORIZATION_APPROVED = true;
    public const AUTHORIZATION_DENIED = false;
    public const AUTHORIZATION_PENDING = null;

    /**
     * @var AuthorizationRequest
     */
    private $authorizationRequest;

    /**
     * @var ?string
     */
    private $resolutionUri;

    /**
     * @var ?bool
     */
    private $authorizationResolution;

    public function __construct(AuthorizationRequest $authorizationRequest)
    {
        $this->authorizationRequest = $authorizationRequest;
    }

    /**
     * @return ?bool
     */
    public function getAuhorizationResolution(): ?bool
    {
        return $this->authorizationResolution;
    }

    public function resolveAuthorization(bool $authorizationResolution)
    {
        $this->authorizationResolution = $authorizationResolution;
    }

    public function getResolutionUri(): string
    {
        if (null === $this->resolutionUri) {
            throw new LogicException('There is no resolution URI. If the authorization request is not approved nor denied, a resolution URI should be provided');
        }

        return $this->resolutionUri;
    }

    public function setResolutionUri(string $resolutionUri): void
    {
        $this->resolutionUri = $resolutionUri;
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
