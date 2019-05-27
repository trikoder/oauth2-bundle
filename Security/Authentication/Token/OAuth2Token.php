<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Security\Authentication\Token;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\User\UserInterface;

final class OAuth2Token extends AbstractToken
{
    public function __construct(ServerRequestInterface $serverRequest, ?UserInterface $user)
    {
        $this->setAttribute('server_request', $serverRequest);

        $roles = $this->buildRolesFromScopes();

        if (null !== $user) {
            // Merge the user's roles with the OAuth 2.0 scopes.
            $roles = array_merge($roles, $user->getRoles());

            $this->setUser($user);
        }

        parent::__construct(array_unique($roles));
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials()
    {
        return $this->getAttribute('server_request')->getAttribute('oauth_access_token_id');
    }

    private function buildRolesFromScopes(): array
    {
        $roles = [];

        foreach ($this->getAttribute('server_request')->getAttribute('oauth_scopes', []) as $scope) {
            $roles[] = sprintf('ROLE_OAUTH2_%s', trim(strtoupper($scope)));
        }

        return $roles;
    }
}
