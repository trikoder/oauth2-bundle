<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Security\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @author Benoit VIGNAL <github@benoit-vignal.fr>
 */
class MissingAuthorizationHeaderException extends AuthenticationException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey(): string
    {
        return 'Authorization Bearer missing..';
    }
}
