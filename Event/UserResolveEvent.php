<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Event;

use Trikoder\Bundle\OAuth2Bundle\Model\Client;
use Trikoder\Bundle\OAuth2Bundle\Model\Grant;

final class UserResolveEvent extends AbstractUserResolveEvent
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var Grant
     */
    private $grant;

    /**
     * @var Client
     */
    private $client;

    public function __construct(string $username, string $password, Grant $grant, Client $client)
    {
        $this->username = $username;
        $this->password = $password;
        $this->grant = $grant;
        $this->client = $client;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getGrant(): Grant
    {
        return $this->grant;
    }

    public function getClient(): Client
    {
        return $this->client;
    }
}
