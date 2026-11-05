<?php

declare(strict_types=1);

namespace Asaas\Sdk\Webhook;

final class WebhookToken
{
    public static function generate(): string
    {
        return bin2hex(random_bytes(32));
    }

    /** Validate new tokens without changing the payload sent by WebhookService. */
    public static function isValid(mixed $token): bool
    {
        return is_string($token)
            && preg_match('/\A[^\s\p{Z}\p{C}]{32,255}\z/u', $token) === 1
            && stripos($token, '$aact_') !== 0;
    }

    /** Existing tokens remain comparable even when they predate the current format. */
    public static function matches(mixed $expected, mixed $provided): bool
    {
        return is_string($expected)
            && $expected !== ''
            && is_string($provided)
            && hash_equals($expected, $provided);
    }

    /**
     * Preserve header names and value shapes while removing the webhook credential.
     *
     * @param array<string, mixed> $headers
     * @return array<string, mixed>
     */
    public static function redactHeaders(array $headers): array
    {
        foreach ($headers as $name => $value) {
            if (strtolower($name) !== 'asaas-access-token') {
                continue;
            }

            $headers[$name] = is_array($value)
                ? array_fill_keys(array_keys($value), '[REDACTED]')
                : '[REDACTED]';
        }

        return $headers;
    }
}
