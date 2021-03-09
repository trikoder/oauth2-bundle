<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Trikoder\Bundle\OAuth2Bundle\Model\RedirectUri;

class RedirectUriTest extends TestCase
{
    public function testPassingUris()
    {
        $passingUris = [
            'http://www.oauth2.com/redirect/to-uri',
            'https://oauth2.com/redirect-to-uri',
            'com.example.test123:/redirect/to-uri',
            'com.example.test:/redirect-to-uri',
            'com.example.test:',
            'http://www.oauth2.com',
        ];

        foreach ($passingUris as $uri) {
            $redirectUri = new RedirectUri($uri);
            $this->assertInstanceOf(RedirectUri::class, $redirectUri);
        }
    }

    public function testFailingUri1()
    {
        $this->expectException(RuntimeException::class);
        $redirectUri = new RedirectUri('comexampletest:/redirect/to-uri');
    }

    public function testFailingUri2()
    {
        $this->expectException(RuntimeException::class);
        $redirectUri = new RedirectUri('someRandomString');
    }

    public function testFailingUri3()
    {
        $this->expectException(RuntimeException::class);
        $redirectUri = new RedirectUri('.com.example.test:/redirect/to-uri');
    }

    public function testFailingUri4()
    {
        $this->expectException(RuntimeException::class);
        $redirectUri = new RedirectUri('com.example.test.:/redirect/to-uri');
    }
}
