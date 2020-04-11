<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Controller;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trikoder\Bundle\OAuth2Bundle\Converter\UserConverterInterface;
use Trikoder\Bundle\OAuth2Bundle\Event\AuthorizationRequestResolveEvent;
use Trikoder\Bundle\OAuth2Bundle\Event\AuthorizationRequestResolveEventFactory;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
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

    /**
     * @var UserConverterInterface
     */
    private $userConverter;

    /**
     * @var ClientManagerInterface
     */
    private $clientManager;

    public function __construct(
        AuthorizationServer $server,
        EventDispatcherInterface $eventDispatcher,
        AuthorizationRequestResolveEventFactory $eventFactory,
        UserConverterInterface $userConverter,
        ClientManagerInterface $clientManager
    ) {
        $this->server = $server;
        $this->eventDispatcher = $eventDispatcher;
        $this->eventFactory = $eventFactory;
        $this->userConverter = $userConverter;
        $this->clientManager = $clientManager;
    }

    public function indexAction(ServerRequestInterface $serverRequest, ResponseFactoryInterface $responseFactory): ResponseInterface
    {
        $serverResponse = $responseFactory->createResponse();

        try {
            $authRequest = $this->server->validateAuthorizationRequest($serverRequest);

            if ('plain' === $authRequest->getCodeChallengeMethod()) {
                $client = $this->clientManager->find($authRequest->getClient()->getIdentifier());
                if (!$client->isPlainTextPkceAllowed()) {
                    return OAuthServerException::invalidRequest(
                        'code_challenge_method',
                        'Plain code challenge method is not allowed for this client'
                    )->generateHttpResponse($serverResponse);
                }
            }

            /** @var AuthorizationRequestResolveEvent $event */
            $event = $this->eventDispatcher->dispatch(
                $this->eventFactory->fromAuthorizationRequest($authRequest),
                OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE
            );

            $authRequest->setUser($this->userConverter->toLeague($event->getUser()));

            if ($event->hasResponse()) {
                return $event->getResponse();
            }

            $authRequest->setAuthorizationApproved($event->getAuthorizationResolution());

            return $this->server->completeAuthorizationRequest($authRequest, $serverResponse);
        } catch (OAuthServerException $e) {
            return $e->generateHttpResponse($serverResponse);
        }
    }
}
