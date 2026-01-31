<?php

declare(strict_types=1);

namespace Asaas\Sdk\Service\Generated;

use Asaas\Sdk\Service\AbstractService;

class PaymentService extends AbstractService
{
    /**
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     */
    public function listPayments(array $query = [], array $headers = []): mixed
    {
        return $this->request('GET', '/payments', [], $query, $headers);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, string> $headers
     */
    public function createPayment(array $payload, array $headers = []): mixed
    {
        return $this->request('POST', '/payments', [], [], $headers, $payload);
    }
}
