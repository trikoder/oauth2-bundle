<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Model;

interface ScopeInterface
{
    public function __toString(): string;
}
