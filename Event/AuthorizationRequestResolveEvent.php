<?php

namespace Trikoder\Bundle\OAuth2Bundle\Event;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use LogicException;
use Symfony\Component\EventDispatcher\Event;

final class AuthorizationRequestResolveEvent extends Event
{
    public const AUTHORIZATION_PENDING = 0;
    public const AUTHORIZATION_APPROVED = 1;
    public const AUTHORIZATION_DENIED = 2;

    public const ALLOWED_RESOLUTIONS = [
        self::AUTHORIZATION_APPROVED,
        self::AUTHORIZATION_DENIED,
    ];

    /**
     * @var AuthorizationRequest
     */
    private $authorizationRequest;

    /**
     * @var ?string
     */
    private $resolutionUri;

    /**
     * @var int
     */
    private $authorizationResolution;

    public function __construct(AuthorizationRequest $authorizationRequest)
    {
        $this->authorizationRequest = $authorizationRequest;
        $this->authorizationResolution = self::AUTHORIZATION_PENDING;
    }

    public function getAuhorizationResolution(): int
    {
        return $this->authorizationResolution;
    }

    public function resolveAuthorization(int $authorizationResolution): void
    {
        if (!\in_array($authorizationResolution, self::ALLOWED_RESOLUTIONS, true)) {
            throw new LogicException('The given resolution code is not allowed.');
        }
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
