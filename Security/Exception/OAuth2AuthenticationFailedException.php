<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Security\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Throwable;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class OAuth2AuthenticationFailedException extends AuthenticationException
{
    public static function create(string $message, ?Throwable $previous = null): self
    {
        return new self($message, 401, $previous);
    }
}
