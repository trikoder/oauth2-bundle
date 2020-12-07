<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Event;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Benoit VIGNAL <github@benoit-vignal.fr>
 */
class AuthenticationScopeFailureEvent extends AuthenticationFailureEvent
{
    /**
     * @var TokenInterface
     */
    private $token;

    public function __construct(AuthenticationException $exception, Response $response, TokenInterface $token)
    {
        parent::__construct($exception, $response);
        $this->token = $token;
    }

    /**
     * @return TokenInterface
     */
    public function getToken(): TokenInterface
    {
        return $this->token;
    }

}
