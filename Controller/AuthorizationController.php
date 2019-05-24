<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Controller;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Trikoder\Bundle\OAuth2Bundle\Event\AuthorizationRequestResolveEvent;
use Trikoder\Bundle\OAuth2Bundle\Event\AuthorizationRequestResolveEventFactory;
use Trikoder\Bundle\OAuth2Bundle\League\Entity\User;
use Trikoder\Bundle\OAuth2Bundle\OAuth2Events;

final class AuthorizationController
{
    /**
     * @var AuthorizationServer
     */
    private $server;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var AuthorizationRequestResolveEventFactory
     */
    private $eventFactory;

    public function __construct(AuthorizationServer $server, EventDispatcherInterface $eventDispatcher, AuthorizationRequestResolveEventFactory $eventFactory)
    {
        $this->server = $server;
        $this->eventDispatcher = $eventDispatcher;
        $this->eventFactory = $eventFactory;
    }

    public function indexAction(ServerRequestInterface $serverRequest, ResponseFactoryInterface $responseFactory): ResponseInterface
    {
        $serverResponse = $responseFactory->createResponse();

        try {
            $authRequest = $this->server->validateAuthorizationRequest($serverRequest);

            /** @var AuthorizationRequestResolveEvent $event */
            $event = $this->eventDispatcher->dispatch(
                OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE,
                $this->eventFactory->fromAuthorizationRequest($authRequest)
            );

            $authRequest->setUser($this->toLeagueUser($event->getUser()));

            if ($event->hasResponse()) {
                return $event->getResponse();
            }

            $authRequest->setAuthorizationApproved($event->getAuthorizationResolution());

            return $this->server->completeAuthorizationRequest($authRequest, $serverResponse);
        } catch (OAuthServerException $e) {
            return $e->generateHttpResponse($serverResponse);
        }
    }

    private function toLeagueUser(UserInterface $user = null): UserEntityInterface
    {
        $userEntity = new User();

        if ($user instanceof UserInterface) {
            $userEntity->setIdentifier($user->getUsername());
        }

        return $userEntity;
    }
}
