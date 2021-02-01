<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Model;

interface RedirectUriInterface
{
    public function __toString(): string;
}
