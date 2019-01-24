<?php

namespace Trikoder\Bundle\OAuth2Bundle\Controller;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
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
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(AuthorizationServer $server, AuthorizationCheckerInterface $authorizationChecker, TokenStorageInterface $tokenStorage, EventDispatcherInterface $eventDispatcher)
    {
        $this->server = $server;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function indexAction(ServerRequestInterface $serverRequest): ResponseInterface
    {
        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new LogicException('There is no logged in user. Review your security config to protect this endpoint.');
        }

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
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            throw new LogicException('There is no security token available. Review your security config to protect endpoint.');
        }

        $user = $token->getUser();
        $username = $user instanceof UserInterface ? $user->getUsername() : (string) $user;

        $userEntity = new User();
        $userEntity->setIdentifier($username);

        return $userEntity;
    }
}
