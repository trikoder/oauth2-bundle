<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Security\Firewall;

use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Trikoder\Bundle\OAuth2Bundle\Security\Authentication\Token\OAuth2Token;
use Trikoder\Bundle\OAuth2Bundle\Security\Exception\InsufficientScopesException;
use Trikoder\Bundle\OAuth2Bundle\Security\Exception\Oauth2AuthenticationFailedException;

final class OAuth2Listener implements ListenerInterface
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
     * @var string
     */
    private $rolePrefix;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        HttpMessageFactoryInterface $httpMessageFactory,
        ?string $rolePrefix = null
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->httpMessageFactory = $httpMessageFactory;
        $this->rolePrefix = $rolePrefix;
    }

    /**
     * BC layer for Symfony < 4.3
     */
    public function handle(GetResponseEvent $event)
    {
        $this->__invoke($event);
    }

    public function __invoke(GetResponseEvent $event)
    {
        $request = $this->httpMessageFactory->createRequest($event->getRequest());

        if (!$request->hasHeader('Authorization')) {
            return;
        }

        try {
            /** @var OAuth2Token $authenticatedToken */
            $authenticatedToken = $this->authenticationManager->authenticate(new OAuth2Token($request, null, $this->rolePrefix));
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
