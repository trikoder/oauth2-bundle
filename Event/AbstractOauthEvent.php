<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Event;

use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Benoit VIGNAL <github@benoit-vignal.fr>
 */
class AbstractOauthEvent extends Event
{
    /**
     * @var OAuthServerException
     */
    protected $exception;

    /**
     * @var ResponseInterface
     */
    protected $response;

    public function __construct(OAuthServerException $exception, ResponseInterface $response)
    {
        $this->exception = $exception;
        $this->response = $response;
    }

    public function getException(): OAuthServerException
    {
        return $this->exception;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function setResponse(ResponseInterface $response): AbstractOauthEvent
    {
        $this->response = $response;
        return $this;
    }
}
