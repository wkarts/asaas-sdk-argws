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
    /**
     * Create a hosted invoice eligible for credit/debit through invoiceUrl.
     * Debit card details cannot be submitted directly to the API. Availability
     * is determined by Asaas. Creating an invoice does not confirm payment.
     *
     * @param array<string, mixed> $payload
     * @param array<string, string> $headers
     */
    public function createHostedCardPayment(array $payload, array $headers = []): mixed
    {
        if (array_intersect(['creditCard', 'creditCardToken', 'creditCardHolderInfo', 'card', 'cardToken', 'cardHolderInfo', 'cardType'], array_keys($payload))) {
            throw new \InvalidArgumentException('Hosted payments must not contain card data or tokens.');
        }
        $billingType = $payload['billingType'] ?? 'CREDIT_CARD';
        if (!in_array($billingType, ['CREDIT_CARD', 'UNDEFINED'], true)) {
            throw new \InvalidArgumentException('Hosted card payments require CREDIT_CARD or UNDEFINED.');
        }
        $payload['billingType'] = $billingType;

        return $this->request('POST', '/v3/payments', headers: $headers, payload: $payload);
    }
}
