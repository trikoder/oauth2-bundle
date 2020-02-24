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
        if (method_exists(parent::class, '__serialize')) {
            // this code path should be the only code path after dropping support for Symfony 3.4
            return [$this->providerKey, parent::__serialize()];
        }

        return [$this->providerKey, $this->getUser(), $this->isAuthenticated(), $this->getRoles(), $this->getAttributes()];
    }

    public function __unserialize(array $data): void
    {
        if (method_exists(parent::class, '__unserialize')) {
            // this code path should be the only code path after dropping support for Symfony 3.4
            [$this->providerKey, $parentData] = $data;
            parent::__unserialize($parentData);

            return;
        }

        [$this->providerKey] = $data;

        unset($data[0]);

        parent::unserialize(array_values($data));
    }

    /**
     *  This entire function can be removed when dropping support for Symfony 3.4
     */
    public function serialize()
    {
        $serialized = [$this->providerKey, parent::serialize(true)];

        if (method_exists(parent::class, 'doSerialize')) {
            return $this->doSerialize($serialized, \func_num_args() ? func_get_arg(0) : null);
        }

        return serialize($serialized);
    }

    /**
     *  This entire function can be removed when dropping support for Symfony 3.4
     */
    public function unserialize($serialized)
    {
        [$this->providerKey, $parentStr] = \is_array($serialized) ? $serialized : unserialize($serialized);
        parent::unserialize($parentStr);
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
