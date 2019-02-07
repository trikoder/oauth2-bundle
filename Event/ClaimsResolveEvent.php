<?php

namespace Trikoder\Bundle\OAuth2Bundle\Event;

use Symfony\Component\EventDispatcher\Event;

final class ClaimsResolveEvent extends Event
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var array
     */
    private $claims = [];

    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getClaims(): array
    {
        return $this->claims;
    }

    /**
     * @param string[] $claims
     */
    public function setClaims(array $claims): self
    {
        $this->claims = $claims;

        return $this;
    }
}
