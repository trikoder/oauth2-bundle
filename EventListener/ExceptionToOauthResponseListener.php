<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\EventListener;


use League\OAuth2\Server\Exception\OAuthServerException;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Trikoder\Bundle\OAuth2Bundle\Security\Exception\ExceptionEventFactory;

class ExceptionToOauthResponseListener
{
    /**
     * @var ExceptionEventFactory
     */
    private $exceptionEventFactory;

    public function __construct(ExceptionEventFactory $exceptionEventFactory)
    {
        $this->exceptionEventFactory = $exceptionEventFactory;
    }

    /**
     * This method will catch and convert all OAuthServerException to a nice ErrorResponse
     * This will also trigger the event system
     *
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if ($exception instanceof OAuthServerException) {
            $updatedEvent = $this->exceptionEventFactory->handleLeagueException($exception);

            $httpFoundationFactory = new HttpFoundationFactory();
            $event->setResponse($httpFoundationFactory->createResponse($updatedEvent->getResponse()));
        }
    }
}
