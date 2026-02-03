<?php

declare(strict_types=1);

namespace Asaas\Sdk\Service\Generated;

use Asaas\Sdk\Service\AbstractService;

class SandboxActionsService extends AbstractService
{
    /**
     * @param array<string, string> $pathParams
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     * @param array<string, mixed>|null $payload
     */
    public function forceExpire(
        array $pathParams = [],
        array $query = [],
        array $headers = [],
        ?array $payload = null
    ): mixed {
        return $this->request(
            'POST',
            '/v3/sandbox/payment/{id}/overdue',
            $pathParams,
            $query,
            $headers,
            $payload,
            false,
            false
        );
    }

    /**
     * @param array<string, string> $pathParams
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     * @param array<string, mixed>|null $payload
     */
    public function confirmPayment(
        array $pathParams = [],
        array $query = [],
        array $headers = [],
        ?array $payload = null
    ): mixed {
        return $this->request(
            'POST',
            '/v3/sandbox/payment/{id}/confirm',
            $pathParams,
            $query,
            $headers,
            $payload,
            false,
            false
        );
    }

}
