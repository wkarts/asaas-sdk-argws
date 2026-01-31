<?php

declare(strict_types=1);

namespace Asaas\Sdk\Util;

use Asaas\Sdk\Exception\ValidationException;

final class Path
{
    /**
     * @param array<string, string> $params
     */
    public static function interpolate(string $template, array $params): string
    {
        return preg_replace_callback('/\{([^}]+)\}/', static function (array $matches) use ($params): string {
            $key = $matches[1];
            if (!array_key_exists($key, $params)) {
                throw new ValidationException(sprintf('Parâmetro de path ausente: %s', $key));
            }

            return rawurlencode((string) $params[$key]);
        }, $template) ?? $template;
    }
}
