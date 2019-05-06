<?php

namespace Trikoder\Bundle\OAuth2Bundle\Manager;

use Trikoder\Bundle\OAuth2Bundle\Model\Grant;
use Trikoder\Bundle\OAuth2Bundle\Model\RedirectUri;
use Trikoder\Bundle\OAuth2Bundle\Model\Scope;

final class ClientFilter
{
    private $grants = null;
    private $redirectUris = null;
    private $scopes = null;

    public static function create(): self
    {
        return new static();
    }

    public function addGrantCriteria(Grant ...$grants): self
    {
        return $this->addCriteria($this->grants, ...$grants);
    }

    public function addRedirectUriCriteria(RedirectUri ...$redirectUris): self
    {
        return $this->addCriteria($this->redirectUris, ...$redirectUris);
    }

    public function addScopeCriteria(Scope ...$scopes): self
    {
        return $this->addCriteria($this->scopes, ...$scopes);
    }

    private function addCriteria(&$field, ...$values): self
    {
        if (0 === \count($values)) {
            return $this;
        }

        if (null === $this->scopes) {
            $field = [];
        }

        $field = array_merge($field, $values);

        return $this;
    }

    public function getGrants(): ?array
    {
        return $this->grants;
    }

    public function getRedirectUris(): ?array
    {
        return $this->redirectUris;
    }

    public function getScopes(): ?array
    {
        return $this->scopes;
    }

    public function hasFilters(): bool
    {
        return
            null !== $this->grants
            || null !== $this->redirectUris
            || null !== $this->scopes;
    }
}
