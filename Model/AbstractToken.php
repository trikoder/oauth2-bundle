<?php


namespace Trikoder\Bundle\OAuth2Bundle\Model;


use DateTimeInterface;

abstract class AbstractToken implements TokenInterface
{
    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var DateTimeInterface
     */
    protected $expiry;

    public function __construct(
        string $identifier,
        DateTimeInterface $expiry
    ) {
        $this->identifier = $identifier;
        $this->expiry = $expiry;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getExpiry(): DateTimeInterface
    {
        return $this->expiry;
    }

    public function __toString(): string
    {
        return $this->getIdentifier();
    }

}
