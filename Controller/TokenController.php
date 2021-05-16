<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Controller;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Trikoder\Bundle\OAuth2Bundle\Security\Exception\ExceptionEventFactory;

final class TokenController
{
    /**
     * @var AuthorizationServer
     */
    private $server;

    /**
     * @var ExceptionEventFactory
     */
    private $exceptionEventFactory;

    public function __construct(AuthorizationServer $server, ExceptionEventFactory $exceptionEventFactory)
    {
        $this->server = $server;
        $this->exceptionEventFactory = $exceptionEventFactory;
    }

    public function indexAction(
        ServerRequestInterface $serverRequest,
        ResponseFactoryInterface $responseFactory
    ) {
        $serverResponse = $responseFactory->createResponse();

        try {
            return $this->server->respondToAccessTokenRequest($serverRequest, $serverResponse);
        } catch (OAuthServerException $e) {
            $event = $this->exceptionEventFactory->handleLeagueException($e);
            return $event->getResponse();
        }
    }
}
