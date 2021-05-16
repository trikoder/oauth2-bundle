<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Security\Guard\Authenticator;

use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AuthenticatorInterface;
use Trikoder\Bundle\OAuth2Bundle\Response\ErrorJsonResponse;
use Trikoder\Bundle\OAuth2Bundle\Security\Authentication\Token\OAuth2Token;
use Trikoder\Bundle\OAuth2Bundle\Security\Authentication\Token\OAuth2TokenFactory;
use Trikoder\Bundle\OAuth2Bundle\Security\Exception\ExceptionEventFactory;
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
    /** @var HttpMessageFactoryInterface */
    private $psr7Request;

    /**
     * @var ExceptionEventFactory
     */
    private $exceptionEventFactory;

    public function __construct(HttpMessageFactoryInterface $httpMessageFactory, ResourceServer $resourceServer, OAuth2TokenFactory $oauth2TokenFactory, ExceptionEventFactory $exceptionEventFactory)
    {
        $this->httpMessageFactory = $httpMessageFactory;
        $this->resourceServer = $resourceServer;
        $this->oauth2TokenFactory = $oauth2TokenFactory;
        $this->exceptionEventFactory = $exceptionEventFactory;
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        $missingAuthHeaderEvent = $this->exceptionEventFactory->invalidClient($request);

        return $missingAuthHeaderEvent->getResponse();
    }

    public function supports(Request $request): bool
    {
        return 0 === strpos($request->headers->get('Authorization', ''), 'Bearer ');
    }

    public function getCredentials(Request $request)
    {
        $psr7Request = $this->httpMessageFactory->createRequest($request);

        // Error will be automatically catch and converted in ExceptionToOauthResponseListener
        $this->psr7Request = $this->resourceServer->validateAuthenticatedRequest($psr7Request);

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
            // In the hint the route scope will be showed
            throw OAuthServerException::invalidScope($this->getRouteScopes());
        }

        $oauth2Token->setAuthenticated(true);

        return $oauth2Token;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $this->psr7Request = null;

        if ($exception instanceof OAuthServerException) {
            $event = $this->exceptionEventFactory->handleLeagueException($exception);
        } else {
            $event = $this->exceptionEventFactory->accessDenied($exception);
        }

        $httpFoundationFactory = new HttpFoundationFactory();
        return $httpFoundationFactory->createResponse($event->getResponse());
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {
        return $this->psr7Request = null;
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }

    private function getRouteScopes(): array
    {
        return $this->psr7Request->getAttribute('oauth2_scopes', []);
    }

    private function isAccessToRouteGranted(OAuth2Token $token): bool
    {
        $routeScopes = $this->getRouteScopes();

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
