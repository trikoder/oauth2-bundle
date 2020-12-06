<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Response\Formatter;

use Symfony\Component\HttpFoundation\Response;

/**
 * @author Benoit VIGNAL <github@benoit-vignal.fr>
 */
interface ResponseFormatterInterface
{
    public function getResponse(string $message, int $httpStatusCode): Response;
}
