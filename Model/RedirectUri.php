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
        $this->assertValidRedirectUri($redirectUri);

        $this->redirectUri = $redirectUri;
    }

    private function assertValidRedirectUri(string $redirectUri): void
    {
        if (filter_var($redirectUri, FILTER_VALIDATE_URL)) {
            return;
        }

        if ($this->isValidCustomURIScheme($redirectUri)) {
            return;
        }

        throw new RuntimeException(sprintf('The \'%s\' string is not a valid URI.', $redirectUri));
    }

    /**
     * @see https://developers.google.com/identity/protocols/oauth2/native-app?hl=en
     */
    private function isValidCustomURIScheme(string $redirectUri): bool
    {
        $parts = parse_url($redirectUri);
        if (isset($parts['host'])) {
            return false;
        }
        if (!isset($parts['scheme'])) {
            return false;
        }

        //Deny if scheme start or end by "."
        if ('.' === substr($parts['scheme'], 0, 1) || '.' === substr($parts['scheme'], -1, 1)) {
            return false;
        }

        //Deny if scheme doesn't have "." domain separator
        if (false === strpos(substr($parts['scheme'], 1, -1), '.')) {
            return false;
        }

        if (isset($parts['path'])) {
            if ('/' !== $parts['path'][0]) {
                return false;
            }
            if (1 < \strlen($parts['path']) && '/' === $parts['path'][1]) {
                return false;
            }
        }

        return true;
    }

    public function __toString(): string
    {
        return $this->redirectUri;
    }
}
