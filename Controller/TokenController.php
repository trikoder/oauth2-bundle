<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Controller;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trikoder\Bundle\OAuth2Bundle\Event\InvalidCredentialsEvent;
use Trikoder\Bundle\OAuth2Bundle\OAuth2Events;
use Trikoder\Bundle\OAuth2Bundle\Response\ErrorJsonResponse;
use Trikoder\Bundle\OAuth2Bundle\Security\Exception\InvalidCredentialsException;

final class TokenController
{
    /**
     * @var AuthorizationServer
     */
    private $server;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(AuthorizationServer $server, EventDispatcherInterface $eventDispatcher)
    {
        $this->server = $server;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function indexAction(
        ServerRequestInterface $serverRequest,
        ResponseFactoryInterface $responseFactory
    ) {
        $serverResponse = $responseFactory->createResponse();

        try {
            return $this->server->respondToAccessTokenRequest($serverRequest, $serverResponse);
        } catch (OAuthServerException $e) {
            return $e->generateHttpResponse($serverResponse);
        } catch (InvalidCredentialsException $e) {
            $response = new ErrorJsonResponse($e->getMessageKey());

            $event = new InvalidCredentialsEvent($e, $response);
            $this->eventDispatcher->dispatch($event, OAuth2Events::INVALID_CREDENTIALS);

            return $response;
        }
    }
}
