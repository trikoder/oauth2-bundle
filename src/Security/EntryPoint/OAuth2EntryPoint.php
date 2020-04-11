<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Security\EntryPoint;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

final class OAuth2EntryPoint implements AuthenticationEntryPointInterface
{
    /**
     * {@inheritdoc}
     */
    public function start(Request $request, ?AuthenticationException $authException = null)
    {
        $exception = new UnauthorizedHttpException('Bearer');

        return new Response('', $exception->getStatusCode(), $exception->getHeaders());
    }
}
