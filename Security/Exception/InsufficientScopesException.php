<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Security\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 * @author Benoit VIGNAL <github@benoit-vignal.fr>
 */
class InsufficientScopesException extends AuthenticationException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey(): string
    {
        return "The token has insufficient scopes.";
    }

}
