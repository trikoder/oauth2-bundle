<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Security\Exception;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 * @author Benoit VIGNAL <github@benoit-vignal.fr>
 */
class InsufficientScopesException extends AuthenticationException
{
    public static function create(TokenInterface $token): self
    {
        $exception = new self('The token has insufficient scopes.', 403);
        $exception->setToken($token);

        return $exception;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageKey(): string
    {
        return "The token has insufficient scopes.";
    }

}
