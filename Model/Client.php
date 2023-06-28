<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Model;

class Client implements ClientInterface
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string|null
     */
    private $secret;

    /**
     * @var RedirectUriInterface[]
     */
    private $redirectUris = [];

    /**
     * @var GrantInterface[]
     */
    private $grants = [];

    /**
     * @var ScopeInterface[]
     */
    private $scopes = [];

    /**
     * @var bool
     */
    private $active = true;

    /**
     * @var bool
     */
    private $allowPlainTextPkce = false;

    public function __construct(string $identifier, ?string $secret)
    {
        $this->identifier = $identifier;
        $this->secret = $secret;
    }

    public function __toString(): string
    {
        return $this->getIdentifier();
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): ClientInterface
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * @return RedirectUriInterface[]
     */
    public function getRedirectUris(): array
    {
        return $this->redirectUris;
    }

    public function setRedirectUris(RedirectUriInterface ...$redirectUris): ClientInterface
    {
        $this->redirectUris = $redirectUris;

        return $this;
    }

    /**
     * @return GrantInterface[]
     */
    public function getGrants(): array
    {
        return $this->grants;
    }

    public function setGrants(GrantInterface ...$grants): ClientInterface
    {
        $this->grants = $grants;

        return $this;
    }

    /**
     * @return ScopeInterface[]
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function setScopes(ScopeInterface ...$scopes): ClientInterface
    {
        $this->scopes = $scopes;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): ClientInterface
    {
        $this->active = $active;

        return $this;
    }

    public function isConfidential(): bool
    {
        return !empty($this->secret);
    }

    public function isPlainTextPkceAllowed(): bool
    {
        return $this->allowPlainTextPkce;
    }

    public function setAllowPlainTextPkce(bool $allowPlainTextPkce): ClientInterface
    {
        $this->allowPlainTextPkce = $allowPlainTextPkce;

        return $this;
    }
}
