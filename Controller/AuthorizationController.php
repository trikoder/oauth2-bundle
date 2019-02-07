<?php

namespace Trikoder\Bundle\OAuth2Bundle\Controller;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Trikoder\Bundle\OAuth2Bundle\Event\AuthorizationRequestResolveEvent;
use Trikoder\Bundle\OAuth2Bundle\League\Entity\User;
use Trikoder\Bundle\OAuth2Bundle\OAuth2Events;
use Zend\Diactoros\Response;

final class AuthorizationController
{
    /**
     * @var AuthorizationServer
     */
    private $server;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(AuthorizationServer $server, TokenStorageInterface $tokenStorage, EventDispatcherInterface $eventDispatcher)
    {
        $this->server = $server;
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function indexAction(ServerRequestInterface $serverRequest): ResponseInterface
    {
        $serverResponse = new Response();

        try {
            $authRequest = $this->server->validateAuthorizationRequest($serverRequest);
            $authRequest->setUser($this->getUserEntity());

            $event = $this->eventDispatcher->dispatch(
                OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE,
                new AuthorizationRequestResolveEvent($authRequest)
            );

            if (AuthorizationRequestResolveEvent::AUTHORIZATION_PENDING === $event->getAuhorizationResolution()) {
                return $serverResponse->withStatus(302)->withHeader('Location', $event->getDecisionUri());
            }

            if (AuthorizationRequestResolveEvent::AUTHORIZATION_APPROVED === $event->getAuhorizationResolution()) {
                $authRequest->setAuthorizationApproved(true);
            }

            return $this->server->completeAuthorizationRequest($authRequest, $serverResponse);
        } catch (OAuthServerException $e) {
            return $e->generateHttpResponse($serverResponse);
        }
    }

    private function getUserEntity(): User
    {
        $userEntity = new User();

        $token = $this->tokenStorage->getToken();
        if ($token instanceof TokenInterface) {
            $user = $token->getUser();
            $username = $user instanceof UserInterface ? $user->getUsername() : (string) $user;
            $userEntity->setIdentifier($username);
        }

        return $userEntity;
    }
}
