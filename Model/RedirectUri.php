<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Model;

use RuntimeException;

class RedirectUri
{
    /**
     * @var string
     */
    private $redirectUri;

    public function __construct(string $redirectUri)
    {
        if (!preg_match('/\b(?:(?:https?|ftp):\/\/|www\.)?[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/', $redirectUri)) {
            throw new RuntimeException(sprintf('The \'%s\' string is not a valid URI.', $redirectUri));
        }

        $this->redirectUri = $redirectUri;
    }

    public function __toString(): string
    {
        return $this->redirectUri;
    }
}
