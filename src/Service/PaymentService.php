<?php

declare(strict_types=1);

namespace Asaas\Sdk\Service;

use Asaas\Sdk\Service\Generated\PaymentService as GeneratedPaymentService;

final class PaymentService extends GeneratedPaymentService
{
    /**
     * Pay an existing charge using the card/cardToken contract (CREDIT or VOUCHER).
     * Never mix this payload with creditCard/creditCardToken. Payment is immediate;
     * on an inconclusive response query the charge before a new explicit attempt.
     *
     * @param array<string, string> $pathParams
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     * @param array<string, mixed>|null $payload
     */
    public function payAChargeWithCard(
        array $pathParams = [],
        array $query = [],
        array $headers = [],
        ?array $payload = null
    ): mixed {
        return $this->request('POST', '/v3/payments/{id}/payWithCard', $pathParams, $query, $headers, $payload);
    }
}
