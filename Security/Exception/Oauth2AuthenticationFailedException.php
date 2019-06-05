<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Security\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Oauth2AuthenticationFailedException extends AuthenticationException
{
    public static function create(string $message): self
    {
        return new self($message, 401);
    }
}
