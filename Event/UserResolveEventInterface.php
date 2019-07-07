<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Event;

use Symfony\Component\Security\Core\User\UserInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;
use Trikoder\Bundle\OAuth2Bundle\Model\Grant;

/**
 * This event occurs when the client requests a "password"
 * grant type from the authorization server.
 *
 * You should set a valid user here if applicable.
 */
interface UserResolveEventInterface
{
    public function getUsername(): string;

    public function getPassword(): string;

    public function getGrant(): Grant;

    public function getClient(): Client;

    public function getUser(): ?UserInterface;

    public function setUser(?UserInterface $user): void;
}
