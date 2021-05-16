<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Security\Exception;

use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Throwable;
use Trikoder\Bundle\OAuth2Bundle\Event\OauthEvent\AbstractOauthEvent;
use Trikoder\Bundle\OAuth2Bundle\Event\OauthEvent\AuthenticationFailureEvent;
use Trikoder\Bundle\OAuth2Bundle\Event\OauthEvent\AuthenticationScopeFailureEvent;
use Trikoder\Bundle\OAuth2Bundle\Event\OauthEvent\AuthorizationServerErrorEvent;
use Trikoder\Bundle\OAuth2Bundle\Event\OauthEvent\InvalidCredentialsEvent;
use Trikoder\Bundle\OAuth2Bundle\Event\OauthEvent\MissingAuthorizationHeaderEvent;
use Trikoder\Bundle\OAuth2Bundle\Security\Authentication\Token\OAuth2Token;

/**
 * @author Benoit VIGNAL <github@benoit-vignal.fr>
 */
class ExceptionEventFactory
{
    protected const MAPPING_LEAGUE_EVENT = [
        "invalid_client"        => MissingAuthorizationHeaderEvent::class,
        "invalid_scope"         => AuthenticationScopeFailureEvent::class,
        "invalid_credentials"   => InvalidCredentialsEvent::class,
        "server_error"          => AuthorizationServerErrorEvent::class,
        "access_denied"         => AuthenticationFailureEvent::class,
    ];

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

    private function generateResponse(OAuthServerException $exception): ResponseInterface
    {
        return $exception->generateHttpResponse($this->responseFactory->createResponse());
    }

    /**
     * Will receive the league exception, find the right event to notify and the return it.
     * Doing this give us the ability to notify other app and being able to apply their custom logic
     *
     * @param OAuthServerException $exception
     */
    public function handleLeagueException(OAuthServerException $exception): AbstractOauthEvent
    {
        if (array_key_exists($exception->getErrorType(), self::MAPPING_LEAGUE_EVENT)) {
            $eventClass = self::MAPPING_LEAGUE_EVENT[$exception->getErrorType()];
            /** @var AbstractOauthEvent $event */
            $event = new $eventClass($exception, $this->generateResponse($exception));
            $this->eventDispatcher->dispatch($event, $event->getEventName());
            return $event;
        } else { // We fallback to a generic event
            $event = new AuthorizationServerErrorEvent($exception, $this->generateResponse($exception));
            $this->eventDispatcher->dispatch($event, $event->getEventName());
            return $event;
        }
    }

    public function invalidClient(ServerRequestInterface $serverRequest): MissingAuthorizationHeaderEvent
    {
        $exception = OAuthServerException::invalidClient($serverRequest);

        $event = new MissingAuthorizationHeaderEvent($exception, $this->generateResponse($exception));
        $this->eventDispatcher->dispatch($event, $event->getEventName());

        return $event;
    }

    public function invalidCredentials(): InvalidCredentialsEvent
    {
        $exception = OAuthServerException::invalidCredentials();

        $event = new InvalidCredentialsEvent($exception, $this->generateResponse($exception));
        $this->eventDispatcher->dispatch($event, $event->getEventName());

        return $event;
    }

    public function accessDenied(Throwable $previous = null): AuthenticationFailureEvent
    {
        $exception = OAuthServerException::accessDenied(null, null, $previous);

        $event = new AuthenticationFailureEvent($exception, $this->generateResponse($exception));
        $this->eventDispatcher->dispatch($event, $event->getEventName());

        return $event;
    }

    public function invalidScope(OAuth2Token $authenticatedToken, string $scope = ""): AuthenticationScopeFailureEvent
    {
        $exception = OAuthServerException::invalidScope($scope);

        $event = new AuthenticationScopeFailureEvent($exception, $this->generateResponse($exception), $authenticatedToken);
        $this->eventDispatcher->dispatch($event, $event->getEventName());

        return $event;
    }
}
