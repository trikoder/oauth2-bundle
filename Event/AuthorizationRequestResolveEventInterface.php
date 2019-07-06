<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Event;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;
use Trikoder\Bundle\OAuth2Bundle\Model\Scope;

/**
 * This event occurs right before the system
 * complete the authorization request.
 *
 * You could approve or deny the authorization request, or set the uri where
 * must be redirected to resolve the authorization request.
 */
interface AuthorizationRequestResolveEventInterface
{
    public const AUTHORIZATION_APPROVED = true;
    public const AUTHORIZATION_DENIED = false;

    public function getAuthorizationResolution(): bool;

    public function resolveAuthorization(bool $authorizationResolution): void;

    public function hasResponse(): bool;

    public function getResponse(): ResponseInterface;

    public function setResponse(ResponseInterface $response): void;

    public function getGrantTypeId(): string;

    public function getClient(): Client;

    public function getUser(): ?UserInterface;

    public function setUser(?UserInterface $user): void;

    /**
     * @return Scope[]
     */
    public function getScopes(): array;

    public function isAuthorizationApproved(): bool;

    public function getRedirectUri(): ?string;

    public function getState(): ?string;

    public function getCodeChallenge(): string;

    public function getCodeChallengeMethod(): string;
}
