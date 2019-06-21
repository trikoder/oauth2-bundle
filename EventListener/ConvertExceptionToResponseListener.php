<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Trikoder\Bundle\OAuth2Bundle\Security\Exception\InsufficientScopesException;
use Trikoder\Bundle\OAuth2Bundle\Security\Exception\Oauth2AuthenticationFailedException;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class ConvertExceptionToResponseListener
{
    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        $exception = $event->getException();
        if ($exception instanceof InsufficientScopesException || $exception instanceof Oauth2AuthenticationFailedException) {
            $event->setResponse(new Response($exception->getMessage(), $exception->getCode()));
        }
    }
}
