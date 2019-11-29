<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Model;

use DateTimeInterface;

class AccessToken extends AbstractToken
{
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

    public function __construct(
        string $identifier,
        DateTimeInterface $expiry,
        Client $client,
        ?string $userIdentifier,
        array $scopes
    ) {
        parent::__construct($identifier, $expiry);
        $this->client = $client;
        $this->userIdentifier = $userIdentifier;
        $this->scopes = $scopes;
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
}
