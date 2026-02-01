<?php

declare(strict_types=1);

namespace Asaas\Sdk\Service;

use Asaas\Sdk\Http\Client;
use Asaas\Sdk\Util\Path;

abstract class AbstractService
{
    public function __construct(protected Client $client) {}

    /**
     * @param array<string, string|int> $pathParams
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     * @param array<string, mixed>|null $payload
     */
    protected function request(
        string $method,
        string $path,
        array $pathParams = [],
        array $query = [],
        array $headers = [],
        ?array $payload = null,
        bool $multipart = false,
        bool $expectsBinary = false
    ): mixed {
        $resolvedPath = Path::interpolate($path, $pathParams);

        return $this->client->request(
            $method,
            $resolvedPath,
            $query,
            $headers,
            $payload,
            $multipart,
            $expectsBinary
        );
    }
}
