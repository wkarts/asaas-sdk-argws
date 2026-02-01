<?php

declare(strict_types=1);

namespace Playground\Utils;

final class Json
{
    /**
     * @return array{data: mixed, error: string|null}
     */
    public static function decode(string $payload): array
    {
        if (trim($payload) === '') {
            return ['data' => null, 'error' => null];
        }

        try {
            $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            return ['data' => null, 'error' => $exception->getMessage()];
        }

        return ['data' => $decoded, 'error' => null];
    }

    public static function pretty(mixed $data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '';
    }
}
