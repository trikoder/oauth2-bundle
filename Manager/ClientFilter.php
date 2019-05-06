<?php

namespace Trikoder\Bundle\OAuth2Bundle\Manager;

final class ClientFilter
{
    private $grants = null;
    private $redirectUris = null;
    private $scopes = null;

    public static function create(): self
    {
        return new static();
    }

    /**
     * @param string|array|null $grantOrGrants
     */
    public function addGrantCriteria($grantOrGrants): self
    {
        return $this->addCriteria($this->grants, $grantOrGrants);
    }

    /**
     * @param string|array|null $redirectUriOrUris
     */
    public function addRedirectUriCriteria($redirectUriOrUris): self
    {
        return $this->addCriteria($this->redirectUris, $redirectUriOrUris);
    }

    /**
     * @param string|array|null $scopeOrScopes
     */
    public function addScopeCriteria($scopeOrScopes): self
    {
        return $this->addCriteria($this->scopes, $scopeOrScopes);
    }

    /**
     * @param string|array|null $valueOrValues
     */
    private function addCriteria(&$field, $valueOrValues): self
    {
        if (null === $valueOrValues) {
            return $this;
        }

        if (false === \is_array($valueOrValues)) {
            $valueOrValues = [$valueOrValues];
        }

        if (null === $this->scopes) {
            $field = [];
        }

        $field = array_merge($field, $valueOrValues);

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
