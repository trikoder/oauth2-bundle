<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Model;

interface ClientInterface
{
    public function __toString(): string;

    public function getIdentifier(): string;

    public function getSecret(): ?string;

    public function setSecret(?string $secret): self;

    public function getRedirectUris(): array;

    public function setRedirectUris(RedirectUriInterface ...$redirectUris): self;

    public function getGrants(): array;

    public function setGrants(GrantInterface ...$grants): self;

    public function getScopes(): array;

    public function setScopes(ScopeInterface ...$scopes): self;

    public function isActive(): bool;

    public function setActive(bool $active): self;

    public function isConfidential(): bool;

    public function isPlainTextPkceAllowed(): bool;

    public function setAllowPlainTextPkce(bool $allowPlainTextPkce): self;
}
