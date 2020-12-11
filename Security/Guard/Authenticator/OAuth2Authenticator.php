<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Security\Guard\Authenticator;

use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AuthenticatorInterface;
use Trikoder\Bundle\OAuth2Bundle\Event\InvalidAuthorizationHeaderEvent;
use Trikoder\Bundle\OAuth2Bundle\OAuth2Events;
use Trikoder\Bundle\OAuth2Bundle\Response\ResponseFormatter;
use Trikoder\Bundle\OAuth2Bundle\Security\Authentication\Token\OAuth2Token;
use Trikoder\Bundle\OAuth2Bundle\Security\Authentication\Token\OAuth2TokenFactory;
use Trikoder\Bundle\OAuth2Bundle\Security\Exception\InsufficientScopesException;
use Trikoder\Bundle\OAuth2Bundle\Security\Exception\InvalidAuthorizationHeaderException;
use Trikoder\Bundle\OAuth2Bundle\Security\User\NullUser;

/**
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 * @author Benoit VIGNAL <github@benoit-vignal.fr>
 */
final class OAuth2Authenticator implements AuthenticatorInterface
{
    private $httpMessageFactory;
    private $resourceServer;
    private $oauth2TokenFactory;
    private $psr7Request;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var ResponseFormatter
     */
    private $responseFormatter;

    public function __construct(HttpMessageFactoryInterface $httpMessageFactory, ResourceServer $resourceServer, OAuth2TokenFactory $oauth2TokenFactory, EventDispatcherInterface $eventDispatcher, ResponseFormatter $responseFormatter)
    {
        $this->httpMessageFactory = $httpMessageFactory;
        $this->resourceServer = $resourceServer;
        $this->oauth2TokenFactory = $oauth2TokenFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->responseFormatter = $responseFormatter;
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        $exception = new InvalidAuthorizationHeaderException();
        $exception->setPreviousException($authException);

        $response = $this->responseFormatter->format($exception->getMessageKey(), Response::HTTP_UNAUTHORIZED);

        $event = new InvalidAuthorizationHeaderEvent($exception, $response);
        $this->eventDispatcher->dispatch($event, OAuth2Events::INVALID_AUTHORIZATION_HEADER);

        return $event->getResponse();
    }

    public function supports(Request $request): bool
    {
        return 0 === strpos($request->headers->get('Authorization', ''), 'Bearer ');
    }

    public function getCredentials(Request $request)
    {
        $psr7Request = $this->httpMessageFactory->createRequest($request);

        try {
            $this->psr7Request = $this->resourceServer->validateAuthenticatedRequest($psr7Request);
        } catch (OAuthServerException $e) {
            throw new AuthenticationException($e->getMessage(), 0, $e);
        }
        return $this->psr7Request->getAttribute('oauth_user_id');
    }

    public function getUser($userIdentifier, UserProviderInterface $userProvider): UserInterface
    {
        return '' === $userIdentifier ? new NullUser() : $userProvider->loadUserByUsername($userIdentifier);
    }

    public function checkCredentials($token, UserInterface $user): bool
    {
        return true;
    }

    public function createAuthenticatedToken(UserInterface $user, $providerKey): OAuth2Token
    {
        $tokenUser = $user instanceof NullUser ? null : $user;

        $oauth2Token = $this->oauth2TokenFactory->createOAuth2Token($this->psr7Request, $tokenUser, $providerKey);

        if (!$this->isAccessToRouteGranted($oauth2Token)) {
            throw InsufficientScopesException::create($oauth2Token);
        }

        $oauth2Token->setAuthenticated(true);

        return $oauth2Token;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $this->psr7Request = null;

        throw $exception;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {
        return $this->psr7Request = null;
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }

    private function isAccessToRouteGranted(OAuth2Token $token): bool
    {
        $routeScopes = $this->psr7Request->getAttribute('oauth2_scopes', []);

        if ([] === $routeScopes) {
            return true;
        }

        $tokenScopes = $token
            ->getAttribute('server_request')
            ->getAttribute('oauth_scopes');

        /*
         * If the end result is empty that means that all route
         * scopes are available inside the issued token scopes.
         */
        return [] === array_diff($routeScopes, $tokenScopes);
    }
}
