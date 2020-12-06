<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Response\Formatter;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Benoit VIGNAL <github@benoit-vignal.fr>
 */
class JsonResponseFormatter implements ResponseFormatterInterface
{
    public function getResponse(string $message, int $httpStatusCode): Response
    {
        return new JsonResponse([
            'message' => $message,
        ], $httpStatusCode);
    }
}
