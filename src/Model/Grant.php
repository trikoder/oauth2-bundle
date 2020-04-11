<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Model;

class Grant
{
    /**
     * @var string
     */
    private $grant;

    public function __construct(string $grant)
    {
        $this->grant = $grant;
    }

    public function __toString(): string
    {
        return $this->grant;
    }
}
