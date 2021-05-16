<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Event\OauthEvent;

use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Benoit VIGNAL <github@benoit-vignal.fr>
 */
abstract class AbstractOauthEvent extends Event
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

    /**
     * @return string The event name that will be use with the eventDispatcher
     */
    abstract function getEventName(): string;

    public function getException(): OAuthServerException
    {
        return $this->exception;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @param ResponseInterface $response
     * @return $this
     */
    public function setResponse(ResponseInterface $response): AbstractOauthEvent
    {
        $this->response = $response;
        return $this;
    }
}
