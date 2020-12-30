<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Benoit VIGNAL <github@benoit-vignal.fr>
 */
class ErrorJsonResponse extends JsonResponse
{
    public function __construct(string $message, int $status = Response::HTTP_UNAUTHORIZED)
    {
        // We force the error body to be always the same
        // In the future we could add a specific error code to help debugging
        parent::__construct(["message" => $message], $status);
    }
}
