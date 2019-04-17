<?php


namespace Trikoder\Bundle\OAuth2Bundle\Manager;


final class ClientFilter
{
    private $grants = null;
    private $redirectUris = null;
    private $scopes = null;

    public static function createFilter(): self
    {
        return new static();
    }

    /**
     * @param string|array $grantOrGrants
     */
    public function addGrantCriteria($grantOrGrants): self
    {
        if (!isset($grantOrGrants)) {
            return $this;
        }

        if (!is_array($grantOrGrants)) {
            $grantOrGrants = [$grantOrGrants];
        }

        if (!$this->grants) {
            $this->grants = [];
        }

        $this->grants = array_merge($this->grants, $grantOrGrants);

        return $this;
    }

    /**
     * @param string|array $redirectUriOrUris
     */
    public function addRedirectUriCriteria($redirectUriOrUris): self
    {
        if (!isset($redirectUriOrUris)) {
            return $this;
        }

        if (!is_array($redirectUriOrUris)) {
            $redirectUriOrUris = [$redirectUriOrUris];
        }

        if (!$this->redirectUris) {
            $this->redirectUris = [];
        }

        $this->redirectUris = array_merge($this->redirectUris, $redirectUriOrUris);

        return $this;
    }

    /**
     * @param string|array $scopeOrScopes
     */
    public function addScopeCriteria($scopeOrScopes): self
    {
        if (!isset($scopeOrScopes)) {
            return $this;
        }

        if (!is_array($scopeOrScopes)) {
            $scopeOrScopes = [$scopeOrScopes];
        }

        if (!$this->redirectUris) {
            $this->scopes = [];
        }

        $this->scopes = array_merge($this->scopes, $scopeOrScopes);

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
            isset($this->grants)
            || isset($this->redirectUris)
            || isset($this->scopes);
    }
}
