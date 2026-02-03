<?php

declare(strict_types=1);

namespace Asaas\Sdk\Service\Generated;

use Asaas\Sdk\Service\AbstractService;

class NotificationService extends AbstractService
{
    /**
     * @param array<string, string> $pathParams
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     * @param array<string, mixed>|null $payload
     */
    public function updateExistingNotification(
        array $pathParams = [],
        array $query = [],
        array $headers = [],
        ?array $payload = null
    ): mixed {
        return $this->request(
            'PUT',
            '/v3/notifications/{id}',
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
    public function updateExistingNotificationsInBatch(
        array $pathParams = [],
        array $query = [],
        array $headers = [],
        ?array $payload = null
    ): mixed {
        return $this->request(
            'PUT',
            '/v3/notifications/batch',
            $pathParams,
            $query,
            $headers,
            $payload,
            false,
            false
        );
    }

}
