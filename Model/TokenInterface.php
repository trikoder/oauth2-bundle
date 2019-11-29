<?php


namespace Trikoder\Bundle\OAuth2Bundle\Model;

use DateTimeInterface;

interface TokenInterface
{
    public function getIdentifier(): string;

    public function getExpiry(): DateTimeInterface;

    public function __toString(): string;
}
