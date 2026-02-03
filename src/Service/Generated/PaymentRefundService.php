<?php

declare(strict_types=1);

namespace Asaas\Sdk\Service\Generated;

use Asaas\Sdk\Service\AbstractService;

class PaymentRefundService extends AbstractService
{
    /**
     * @param array<string, string> $pathParams
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     * @param array<string, mixed>|null $payload
     */
    public function retrieveRefundsOfASinglePayment(
        array $pathParams = [],
        array $query = [],
        array $headers = [],
        ?array $payload = null
    ): mixed {
        return $this->request(
            'GET',
            '/v3/payments/{id}/refunds',
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
    public function refundBankSlip(
        array $pathParams = [],
        array $query = [],
        array $headers = [],
        ?array $payload = null
    ): mixed {
        return $this->request(
            'POST',
            '/v3/payments/{id}/bankSlip/refund',
            $pathParams,
            $query,
            $headers,
            $payload,
            false,
            false
        );
    }

}
