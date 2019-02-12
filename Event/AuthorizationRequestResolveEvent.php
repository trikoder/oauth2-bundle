<?php

namespace Trikoder\Bundle\OAuth2Bundle\Event;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Symfony\Component\EventDispatcher\Event;
use Trikoder\Bundle\OAuth2Bundle\League\Entity\User;
use Zend\Diactoros\Response;

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
     * @var Response
     */
    private $response;

    public function __construct(AuthorizationRequest $authorizationRequest)
    {
        $this->authorizationRequest = $authorizationRequest;
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

    public function setUser(User $user): void
    {
        $this->authorizationRequest->setUser($user);
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
     * @return void
     */
    public function approveAuthorization()
    {
        $this->authorizationRequest->setAuthorizationApproved(true);
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

    /**
     * @return Response
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    /**
     * @return bool
     */
    public function hasResponse(): bool
    {
        return $this->response !== null;
    }
}
