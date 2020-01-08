<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Security\Firewall;

use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Trikoder\Bundle\OAuth2Bundle\Security\Authentication\Token\OAuth2Token;
use Trikoder\Bundle\OAuth2Bundle\Security\Authentication\Token\OAuth2TokenFactory;
use Trikoder\Bundle\OAuth2Bundle\Security\Exception\InsufficientScopesException;
use Trikoder\Bundle\OAuth2Bundle\Security\Exception\Oauth2AuthenticationFailedException;

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

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        HttpMessageFactoryInterface $httpMessageFactory,
        OAuth2TokenFactory $oauth2TokenFactory
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->httpMessageFactory = $httpMessageFactory;
        $this->oauth2TokenFactory = $oauth2TokenFactory;
    }

    public function __invoke(RequestEvent $event)
    {
        $request = $this->httpMessageFactory->createRequest($event->getRequest());

        if (!$request->hasHeader('Authorization')) {
            return;
        }

        try {
            /** @var OAuth2Token $authenticatedToken */
            $authenticatedToken = $this->authenticationManager->authenticate($this->oauth2TokenFactory->createOAuth2Token($request, null));
        } catch (AuthenticationException $e) {
            throw Oauth2AuthenticationFailedException::create($e->getMessage());
        }

        if (!$this->isAccessToRouteGranted($event->getRequest(), $authenticatedToken)) {
            throw InsufficientScopesException::create($authenticatedToken);
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
