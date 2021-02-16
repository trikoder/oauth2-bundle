<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Manager;

use Trikoder\Bundle\OAuth2Bundle\Model\GrantInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\RedirectUriInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\ScopeInterface;

final class ClientFilter
{
    /**
     * @var GrantInterface[]
     */
    private $grants = [];

    /**
     * @var RedirectUriInterface[]
     */
    private $redirectUris = [];

    /**
     * @var ScopeInterface[]
     */
    private $scopes = [];

    public static function create(): self
    {
        return new static();
    }

    public function addGrantCriteria(GrantInterface ...$grants): self
    {
        return $this->addCriteria($this->grants, ...$grants);
    }

    public function addRedirectUriCriteria(RedirectUriInterface ...$redirectUris): self
    {
        return $this->addCriteria($this->redirectUris, ...$redirectUris);
    }

    public function addScopeCriteria(ScopeInterface ...$scopes): self
    {
        return $this->addCriteria($this->scopes, ...$scopes);
    }

    private function addCriteria(&$field, ...$values): self
    {
        if (0 === \count($values)) {
            return $this;
        }

        $field = array_merge($field, $values);

        return $this;
    }

    /**
     * @return GrantInterface[]
     */
    public function getGrants(): array
    {
        return $this->grants;
    }

    /**
     * @return RedirectUriInterface[]
     */
    public function getRedirectUris(): array
    {
        return $this->redirectUris;
    }

    /**
     * @return ScopeInterface[]
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function hasFilters(): bool
    {
        return
            !empty($this->grants)
            || !empty($this->redirectUris)
            || !empty($this->scopes);
    }
}
