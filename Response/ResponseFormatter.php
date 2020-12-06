<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Response;

use Symfony\Component\HttpFoundation\Response;
use Trikoder\Bundle\OAuth2Bundle\Response\Formatter\DisabledResponseFormatter;
use Trikoder\Bundle\OAuth2Bundle\Response\Formatter\JsonResponseFormatter;
use Trikoder\Bundle\OAuth2Bundle\Response\Formatter\ResponseFormatterInterface;

/**
 * @author Benoit VIGNAL <github@benoit-vignal.fr>
 */
class ResponseFormatter
{
    private const DEFAULT_FORMATTER = [
        'disabled' => DisabledResponseFormatter::class,
        'json' => JsonResponseFormatter::class,
    ];

    /**
     * @var string
     */
    private $responseFormatter;

    public function __construct(string $responseFormatter)
    {
        $this->responseFormatter = $responseFormatter;
    }

    /**
     * Format the message into an unified response type
     *
     * @throws \Exception
     */
    public function format(string $message, int $httpStatusCode): Response
    {
        $formatter = $this->getFormatter();

        return $formatter->getResponse($message, $httpStatusCode);
    }

    private function getFormatter(): ResponseFormatterInterface
    {
        // The formatter is one supported out of the box
        if (\array_key_exists($this->responseFormatter, self::DEFAULT_FORMATTER)) {
            $responseFormatter = self::DEFAULT_FORMATTER[$this->responseFormatter];
            /* @var ResponseFormatterInterface $responseFormatter */
            return new $responseFormatter();
        }

        // User defined formatter
        if (class_exists($this->responseFormatter)) {
            if (!\in_array($this->responseFormatter, class_implements($this->responseFormatter))) {
                return new $this->responseFormatter();
            } else {
                throw new \Exception("\"{$this->responseFormatter}\" must implement \"" . ResponseFormatterInterface::class . '"');
            }
        }

        throw new \Exception('Unsupported ResponseFormatter type.');
    }
}
