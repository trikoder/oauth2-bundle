<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Security\Authentication\Token;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\User\UserInterface;

final class OAuth2Token extends AbstractToken
{
    /**
     * @var string
     */
    private $providerKey;

    public function __construct(
        ServerRequestInterface $serverRequest,
        ?UserInterface $user,
        string $rolePrefix,
        string $providerKey
    ) {
        $this->setAttribute('server_request', $serverRequest);
        $this->setAttribute('role_prefix', $rolePrefix);

        $roles = $this->buildRolesFromScopes();

        if (null !== $user) {
            // Merge the user's roles with the OAuth 2.0 scopes.
            $roles = array_merge($roles, $user->getRoles());

            $this->setUser($user);
        }

        parent::__construct(array_unique($roles));

        $this->providerKey = $providerKey;
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials()
    {
        return $this->getAttribute('server_request')->getAttribute('oauth_access_token_id');
    }

    public function getProviderKey(): string
    {
        return $this->providerKey;
    }

    public function __serialize(): array
    {
        return [$this->providerKey, parent::__serialize()];
    }

    public function __unserialize(array $data): void
    {
        [$this->providerKey, $parentData] = $data;
        parent::__unserialize($parentData);
    }

    private function buildRolesFromScopes(): array
    {
        $prefix = $this->getAttribute('role_prefix');
        $roles = [];

        foreach ($this->getAttribute('server_request')->getAttribute('oauth_scopes', []) as $scope) {
            $roles[] = strtoupper(trim($prefix . $scope));
        }

        return $roles;
    }
}
