<?php

namespace Trikoder\Bundle\OAuth2Bundle\Event;

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
    private $decisionUri;

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

    public function getDecisionUri(): string
    {
        if (null === $this->decisionUri) {
            throw new LogicException('There is no decision URI. If the authorization request is not approved nor denied, a decision URI should be provided');
        }

        return $this->decisionUri;
    }

    public function setDecisionUri(string $decisionUri)
    {
        $this->decisionUri = $decisionUri;
    }

    /**
     * @return string
     */
    public function getGrantTypeId()
    {
        return $this->authorizationRequest->getGrantTypeId();
    }

    /**
     * @return ClientEntityInterface
     */
    public function getClient()
    {
        return $this->authorizationRequest->getClient();
    }

    /**
     * @return UserEntityInterface
     */
    public function getUser()
    {
        return $this->authorizationRequest->getUser();
    }

    /**
     * @return ScopeEntityInterface[]
     */
    public function getScopes()
    {
        return $this->authorizationRequest->getScopes();
    }

    /**
     * @return bool
     */
    public function isAuthorizationApproved()
    {
        return $this->authorizationRequest->isAuthorizationApproved();
    }

    /**
     * @return string|null
     */
    public function getRedirectUri()
    {
        return $this->authorizationRequest->getRedirectUri();
    }

    /**
     * @return string|null
     */
    public function getState()
    {
        return $this->authorizationRequest->getState();
    }

    /**
     * @return string
     */
    public function getCodeChallenge()
    {
        return $this->authorizationRequest->getCodeChallenge();
    }

    /**
     * @return string
     */
    public function getCodeChallengeMethod()
    {
        return $this->authorizationRequest->getCodeChallengeMethod();
    }
}
