<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Model;

use DateTimeInterface;

interface TokenInterface
{
    public function getIdentifier(): string;

    public function getExpiry(): DateTimeInterface;

    public function __toString(): string;
}
