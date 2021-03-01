<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Trikoder\Bundle\OAuth2Bundle\Model\RedirectUri;

class RedirectUriTest extends TestCase
{
    public function testUris()
    {
        $passingUris = [
            'http://www.oauth2.com/redirect/to-uri',
            'https://oauth2.com/redirect-to-uri',
            'com.example.test123:/redirect/to-uri',
            'com.example.test:/redirect-to-uri',
        ];

        $faillingUris = [
            'someRandomString',
        ];

        foreach ($passingUris as $uri) {
            $redirectUri = new RedirectUri($uri);
            $this->assertInstanceOf(RedirectUri::class, $redirectUri);
        }

        foreach ($faillingUris as $uri) {
            $this->expectException(RuntimeException::class);
            $redirectUri = new RedirectUri($uri);
        }
    }
}
