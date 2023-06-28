<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Model;

use DateTimeInterface;

class AccessToken implements AccessTokenInterface
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var DateTimeInterface
     */
    private $expiry;

    /**
     * @var string|null
     */
    private $userIdentifier;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var ScopeInterface[]
     */
    private $scopes = [];

    /**
     * @var bool
     */
    private $revoked = false;

    public function __construct(
        string $identifier,
        DateTimeInterface $expiry,
        ClientInterface $client,
        ?string $userIdentifier,
        array $scopes
    ) {
        $this->identifier = $identifier;
        $this->expiry = $expiry;
        $this->client = $client;
        $this->userIdentifier = $userIdentifier;
        $this->scopes = $scopes;
    }

    public function __toString(): string
    {
        return $this->getIdentifier();
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getExpiry(): DateTimeInterface
    {
        return $this->expiry;
    }

    public function getUserIdentifier(): ?string
    {
        return $this->userIdentifier;
    }

    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    /**
     * @return Scope[]
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function isRevoked(): bool
    {
        return $this->revoked;
    }

    public function revoke(): AccessTokenInterface
    {
        $this->revoked = true;

        return $this;
    }
}
