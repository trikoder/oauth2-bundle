<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Event;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Benoit VIGNAL <github@benoit-vignal.fr>
 */
class AuthenticationFailureEvent extends Event
{
    /**
     * @var AuthenticationException
     */
    protected $exception;

    /**
     * @var ResponseInterface
     */
    protected $response;

    public function __construct(AuthenticationException $exception, ResponseInterface $response)
    {
        $this->exception = $exception;
        $this->response = $response;
    }

    public function getException(): AuthenticationException
    {
        return $this->exception;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }
}
