<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Security\Authentication\Provider;

use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use RuntimeException;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Trikoder\Bundle\OAuth2Bundle\Security\Authentication\Token\OAuth2Token;

final class OAuth2Provider implements AuthenticationProviderInterface
{
    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var ResourceServer
     */
    private $resourceServer;

    /**
     * @var string
     */
    private $rolePrefix;

    public function __construct(UserProviderInterface $userProvider, ResourceServer $resourceServer, string $rolePrefix = null)
    {
        $this->userProvider = $userProvider;
        $this->resourceServer = $resourceServer;
        $this->rolePrefix = $rolePrefix;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            throw new RuntimeException(
                sprintf(
                    'This authentication provider can only handle tokes of type \'%s\'.',
                    OAuth2Token::class
                )
            );
        }

        try {
            $request = $this->resourceServer->validateAuthenticatedRequest(
                $token->getAttribute('server_request')
            );
        } catch (OAuthServerException $e) {
            throw new AuthenticationException('The resource server rejected the request.', 0, $e);
        }

        $user = $this->getAuthenticatedUser(
            $request->getAttribute('oauth_user_id')
        );

        $token = new OAuth2Token($request, $user, $this->rolePrefix);
        $token->setAuthenticated(true);

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof OAuth2Token;
    }

    private function getAuthenticatedUser(string $userIdentifier): ?UserInterface
    {
        if ('' === $userIdentifier) {
            /*
             * If the identifier is an empty string, that means that the
             * access token isn't bound to a user defined in the system.
             */
            return null;
        }

        return $this->userProvider->loadUserByUsername($userIdentifier);
    }
}
