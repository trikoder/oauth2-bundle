<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Security\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 * @author Benoit VIGNAL <github@benoit-vignal.fr>
 */
class OAuth2AuthenticationFailedException extends AuthenticationException
{
    private $previousException;

    /**
     * {@inheritdoc}
     */
    public function getMessageKey(): string
    {
        return 'Invalid OAuth Token.';
    }

    public function getPreviousException(): ?\Exception
    {
        return $this->previousException;
    }

    public function setPreviousException(?\Exception $previousException): void
    {
        $this->previousException = $previousException;
    }
}
