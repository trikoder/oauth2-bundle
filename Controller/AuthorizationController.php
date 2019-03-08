<?php

namespace Trikoder\Bundle\OAuth2Bundle\Controller;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Security;
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
     * @var Security
     */
    private $security;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(AuthorizationServer $server, Security $security, EventDispatcherInterface $eventDispatcher)
    {
        $this->server = $server;
        $this->security = $security;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function indexAction(ServerRequestInterface $serverRequest): ResponseInterface
    {
        $serverResponse = new Response();

        try {
            $authRequest = $this->server->validateAuthorizationRequest($serverRequest);
            $authRequest->setUser($this->getUserEntity());

            /** @var AuthorizationRequestResolveEvent $event */
            $event = $this->eventDispatcher->dispatch(
                OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE,
                new AuthorizationRequestResolveEvent($authRequest)
            );

            if ($event->hasResponse()) {
                return $event->getResponse();
            }

            $authRequest->setAuthorizationApproved($event->getAuhorizationResolution());

            return $this->server->completeAuthorizationRequest($authRequest, $serverResponse);
        } catch (OAuthServerException $e) {
            return $e->generateHttpResponse($serverResponse);
        }
    }

    private function getUserEntity(): User
    {
        $userEntity = new User();

        $user = $this->security->getUser();
        if ($user instanceof UserInterface) {
            $userEntity->setIdentifier($user->getUsername());
        }

        return $userEntity;
    }
}
