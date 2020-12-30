<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Security\Firewall;

use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Trikoder\Bundle\OAuth2Bundle\Event\AuthenticationFailureEvent;
use Trikoder\Bundle\OAuth2Bundle\Event\AuthenticationScopeFailureEvent;
use Trikoder\Bundle\OAuth2Bundle\Event\InvalidAuthorizationHeaderEvent;
use Trikoder\Bundle\OAuth2Bundle\OAuth2Events;
use Trikoder\Bundle\OAuth2Bundle\Response\ErrorJsonResponse;
use Trikoder\Bundle\OAuth2Bundle\Security\Authentication\Token\OAuth2Token;
use Trikoder\Bundle\OAuth2Bundle\Security\Authentication\Token\OAuth2TokenFactory;
use Trikoder\Bundle\OAuth2Bundle\Security\Exception\InsufficientScopesException;
use Trikoder\Bundle\OAuth2Bundle\Security\Exception\InvalidAuthorizationHeaderException;
use Trikoder\Bundle\OAuth2Bundle\Security\Exception\OAuth2AuthenticationFailedException;

final class OAuth2Listener
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var AuthenticationManagerInterface
     */
    private $authenticationManager;

    /**
     * @var HttpMessageFactoryInterface
     */
    private $httpMessageFactory;

    /**
     * @var OAuth2TokenFactory
     */
    private $oauth2TokenFactory;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var string
     */
    private $providerKey;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        HttpMessageFactoryInterface $httpMessageFactory,
        EventDispatcherInterface $eventDispatcher,
        OAuth2TokenFactory $oauth2TokenFactory,
        string $providerKey
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->httpMessageFactory = $httpMessageFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->oauth2TokenFactory = $oauth2TokenFactory;
        $this->providerKey = $providerKey;
    }

    public function __invoke(RequestEvent $event)
    {
        $request = $this->httpMessageFactory->createRequest($event->getRequest());

        if (!$request->hasHeader('Authorization')) {
            $exception = new InvalidAuthorizationHeaderException();
            $response = new ErrorJsonResponse($exception->getMessageKey());
            $response->headers->set('WWW-Authenticate', 'Bearer');

            $missingAuthHeaderEvent = new InvalidAuthorizationHeaderEvent($exception, $response);
            $this->eventDispatcher->dispatch($missingAuthHeaderEvent, OAuth2Events::INVALID_AUTHORIZATION_HEADER);

            $event->setResponse($missingAuthHeaderEvent->getResponse());

            return;
        }

        try {
            /** @var OAuth2Token $authenticatedToken */
            $authenticatedToken = $this->authenticationManager->authenticate($this->oauth2TokenFactory->createOAuth2Token($request, null, $this->providerKey));
        } catch (AuthenticationException $e) {
            $exception = new OAuth2AuthenticationFailedException();
            $exception->setPreviousException($e);
            $response = new ErrorJsonResponse($exception->getMessageKey());

            $authenticationFailureEvent = new AuthenticationFailureEvent($exception, $response);
            $this->eventDispatcher->dispatch($authenticationFailureEvent, OAuth2Events::AUTHENTICATION_FAILURE);

            $event->setResponse($authenticationFailureEvent->getResponse());

            return;
        }

        if (!$this->isAccessToRouteGranted($event->getRequest(), $authenticatedToken)) {
            $exception = new InsufficientScopesException();
            $exception->setToken($authenticatedToken);

            $response = new ErrorJsonResponse($exception->getMessageKey(), Response::HTTP_FORBIDDEN);

            $authenticationFailureScopeEvent = new AuthenticationScopeFailureEvent($exception, $response, $authenticatedToken);
            $this->eventDispatcher->dispatch($authenticationFailureScopeEvent, OAuth2Events::AUTHENTICATION_SCOPE_FAILURE);

            $event->setResponse($authenticationFailureScopeEvent->getResponse());

            return;
        }

        $this->tokenStorage->setToken($authenticatedToken);
    }

    private function isAccessToRouteGranted(Request $request, OAuth2Token $token): bool
    {
        $routeScopes = $request->attributes->get('oauth2_scopes', []);

        if (empty($routeScopes)) {
            return true;
        }

        $tokenScopes = $token
            ->getAttribute('server_request')
            ->getAttribute('oauth_scopes');

        /*
         * If the end result is empty that means that all route
         * scopes are available inside the issued token scopes.
         */
        return empty(
            array_diff(
                $routeScopes,
                $tokenScopes
            )
        );
    }
}
