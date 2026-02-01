<?php

declare(strict_types=1);

namespace Playground\Utils;

final class Mask
{
    private const SENSITIVE_KEYS = [
        'api_key',
        'access_token',
        'token',
        'password',
        'secret',
        'key',
    ];

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function maskArray(array $data): array
    {
        $masked = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $masked[$key] = self::maskArray($value);
                continue;
            }

            if (self::isSensitiveKey((string) $key) && is_string($value)) {
                $masked[$key] = self::maskString($value);
                continue;
            }

            $masked[$key] = $value;
        }

        return $masked;
    }

    public static function maskString(string $value): string
    {
        $length = strlen($value);
        if ($length <= 6) {
            return str_repeat('*', $length);
        }

        $start = substr($value, 0, 3);
        $end = substr($value, -3);

        return $start . str_repeat('*', $length - 6) . $end;
    }

    private static function isSensitiveKey(string $key): bool
    {
        $key = strtolower($key);
        foreach (self::SENSITIVE_KEYS as $needle) {
            if (str_contains($key, $needle)) {
                return true;
            }
        }

        return false;
    }
}
