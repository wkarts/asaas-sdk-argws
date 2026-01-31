<?php

declare(strict_types=1);

namespace Asaas\Sdk\Http;

use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class RetryMiddleware
{
    public static function create(int $maxRetries = 3, int $baseDelayMs = 200): callable
    {
        return Middleware::retry(
            static function (
                int $retries,
                RequestInterface $request,
                ?ResponseInterface $response = null,
                ?\Throwable $exception = null
            ) use ($maxRetries): bool {
                if ($retries >= $maxRetries) {
                    return false;
                }

                if ($exception !== null) {
                    return true;
                }

                if ($response === null) {
                    return false;
                }

                $status = $response->getStatusCode();

                return $status === 429 || $status >= 500;
            },
            static function (int $retries) use ($baseDelayMs): int {
                return (int) ($baseDelayMs * (2 ** $retries));
            }
        );
    }
}
