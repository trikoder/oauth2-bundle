<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Model;

use DateTimeInterface;

class AuthorizationCode
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
     * @var Client
     */
    private $client;

    /**
     * @var Scope[]
     */
    private $scopes = [];

    /**
     * @var bool
     */
    private $revoked = false;

    /**
     * @var string|null
     */
    private $nonce;

    public function __construct(
        string $identifier,
        DateTimeInterface $expiry,
        Client $client,
        ?string $userIdentifier,
        array $scopes)
    {
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

    public function getExpiryDateTime(): DateTimeInterface
    {
        return $this->expiry;
    }

    public function getUserIdentifier(): ?string
    {
        return $this->userIdentifier;
    }

    public function getClient(): Client
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

    public function revoke(): self
    {
        $this->revoked = true;

        return $this;
    }

    public function getNonce(): ?string
    {
        return $this->nonce;
    }

    public function setNonce(string $nonce): self
    {
        if ($this->nonce === null) {
            $this->nonce = $nonce;
        }

        return $this;
    }
}
