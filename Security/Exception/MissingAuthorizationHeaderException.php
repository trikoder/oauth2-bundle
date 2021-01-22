<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Security\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @author Benoit VIGNAL <github@benoit-vignal.fr>
 */
class MissingAuthorizationHeaderException extends AuthenticationException
{
    private $previousException;

    /**
     * {@inheritdoc}
     */
    public function getMessageKey(): string
    {
        return 'Missing Authorization Bearer.';
    }

    public function getPreviousException(): ?AuthenticationException
    {
        return $this->previousException;
    }

    public function setPreviousException(?AuthenticationException $previousException): void
    {
        $this->previousException = $previousException;
    }
}
