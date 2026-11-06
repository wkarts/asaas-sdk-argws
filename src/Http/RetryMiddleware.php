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
                // Card payment is not idempotent. This applies even if the caller
                // supplies an idempotency header: the provider does not promise it.
                if (strtoupper($request->getMethod()) === 'POST') {
                    $path = $request->getUri()->getPath();
                    if (preg_match('~/payments/[^/]+/payWith(?:Credit)?Card/?$~D', $path)) {
                        return false;
                    }
                    if (preg_match('~/payments/?$~D', $path)) {
                        $body = json_decode((string) $request->getBody(), true);
                        if (is_array($body) && array_intersect(['creditCard', 'creditCardToken', 'card', 'cardToken'], array_keys($body))) {
                            return false;
                        }
                    }
                }

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
