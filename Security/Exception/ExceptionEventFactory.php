<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Security\Exception;

use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Trikoder\Bundle\OAuth2Bundle\Event\AuthenticationFailureEvent;
use Trikoder\Bundle\OAuth2Bundle\Event\AuthenticationScopeFailureEvent;
use Trikoder\Bundle\OAuth2Bundle\Event\MissingAuthorizationHeaderEvent;
use Trikoder\Bundle\OAuth2Bundle\OAuth2Events;
use Trikoder\Bundle\OAuth2Bundle\Security\Authentication\Token\OAuth2Token;

class ExceptionEventFactory
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    public function __construct(EventDispatcherInterface $eventDispatcher, ResponseFactoryInterface $responseFactory)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->responseFactory = $responseFactory;
    }

    public function invalidClient(ServerRequestInterface $serverRequest): MissingAuthorizationHeaderEvent
    {
        $exception = OAuthServerException::invalidClient($serverRequest);

        $event = new MissingAuthorizationHeaderEvent($exception, $exception->generateHttpResponse($this->responseFactory->createResponse()));
        $this->eventDispatcher->dispatch($event, OAuth2Events::MISSING_AUTHORIZATION_HEADER);

        return $event;
    }

    public function accessDenied(Throwable $previous = null): AuthenticationFailureEvent
    {
        $exception = OAuthServerException::accessDenied(null, null, $previous);

        $event = new AuthenticationFailureEvent($exception, $exception->generateHttpResponse($this->responseFactory->createResponse()));
        $this->eventDispatcher->dispatch($event, OAuth2Events::AUTHENTICATION_FAILURE);

        return $event;
    }

    public function invalidScope(OAuth2Token $authenticatedToken): AuthenticationScopeFailureEvent
    {
        $exception = OAuthServerException::invalidScope("");

        $event = new AuthenticationScopeFailureEvent($exception, $exception->generateHttpResponse($this->responseFactory->createResponse()), $authenticatedToken);
        $this->eventDispatcher->dispatch($event, OAuth2Events::AUTHENTICATION_SCOPE_FAILURE);

        return $event;
    }
}
