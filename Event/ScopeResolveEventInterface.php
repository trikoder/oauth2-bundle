<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Event;

use Trikoder\Bundle\OAuth2Bundle\Model\Client;
use Trikoder\Bundle\OAuth2Bundle\Model\Grant;
use Trikoder\Bundle\OAuth2Bundle\Model\Scope;

/**
 * This event occurs right before the user obtains their
 * valid access token.
 *
 * You could alter the access token's scope here.
 */
interface ScopeResolveEventInterface
{
    /**
     * @return Scope[]
     */
    public function getScopes(): array;

    public function setScopes(Scope ...$scopes): void;

    public function getGrant(): Grant;

    public function getClient(): Client;

    public function getUserIdentifier(): ?string;
}
