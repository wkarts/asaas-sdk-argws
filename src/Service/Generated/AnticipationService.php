<?php

declare(strict_types=1);

namespace Asaas\Sdk\Service\Generated;

use Asaas\Sdk\Service\AbstractService;

class AnticipationService extends AbstractService
{
    /**
     * @param array<string, string> $pathParams
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     * @param array<string, mixed>|null $payload
     */
    public function requestAnticipation(
        array $pathParams = [],
        array $query = [],
        array $headers = [],
        ?array $payload = null
    ): mixed {
        return $this->request(
            'POST',
            '/v3/anticipations',
            $pathParams,
            $query,
            $headers,
            $payload,
            true,
            false
        );
    }

    /**
     * @param array<string, string> $pathParams
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     * @param array<string, mixed>|null $payload
     */
    public function listAnticipations(
        array $pathParams = [],
        array $query = [],
        array $headers = [],
        ?array $payload = null
    ): mixed {
        return $this->request(
            'GET',
            '/v3/anticipations',
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
    public function updateStatusOfAutomaticAnticipation(
        array $pathParams = [],
        array $query = [],
        array $headers = [],
        ?array $payload = null
    ): mixed {
        return $this->request(
            'PUT',
            '/v3/anticipations/configurations',
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
    public function retrieveStatusOfAutomaticAnticipation(
        array $pathParams = [],
        array $query = [],
        array $headers = [],
        ?array $payload = null
    ): mixed {
        return $this->request(
            'GET',
            '/v3/anticipations/configurations',
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
    public function cancelAnticipation(
        array $pathParams = [],
        array $query = [],
        array $headers = [],
        ?array $payload = null
    ): mixed {
        return $this->request(
            'POST',
            '/v3/anticipations/{id}/cancel',
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
    public function retrieveASingleAnticipation(
        array $pathParams = [],
        array $query = [],
        array $headers = [],
        ?array $payload = null
    ): mixed {
        return $this->request(
            'GET',
            '/v3/anticipations/{id}',
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
    public function retrieveAnticipationLimits(
        array $pathParams = [],
        array $query = [],
        array $headers = [],
        ?array $payload = null
    ): mixed {
        return $this->request(
            'GET',
            '/v3/anticipations/limits',
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
    public function simulateAnticipation(
        array $pathParams = [],
        array $query = [],
        array $headers = [],
        ?array $payload = null
    ): mixed {
        return $this->request(
            'POST',
            '/v3/anticipations/simulate',
            $pathParams,
            $query,
            $headers,
            $payload,
            false,
            false
        );
    }

}
