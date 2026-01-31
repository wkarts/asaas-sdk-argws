<?php

declare(strict_types=1);

namespace Asaas\Sdk\Util;

final class Query
{
    /**
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    public static function normalize(array $query): array
    {
        $normalized = [];

        foreach ($query as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (is_bool($value)) {
                $normalized[$key] = $value ? 'true' : 'false';
                continue;
            }

            if (is_array($value)) {
                $normalized[$key] = $value;
                continue;
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }
}
