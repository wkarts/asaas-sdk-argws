# Card payment contracts

The official API distinguishes two immediate payment operations for an existing
charge. `PaymentService::payAChargeWithCreditCard` preserves the existing
`creditCard`/`creditCardHolderInfo` or `creditCardToken` contract.
`PaymentService::payAChargeWithCard` adds `/v3/payments/{id}/payWithCard` with
`cardType` (`CREDIT` or `VOUCHER`) and `card` or `cardToken`. Payloads are passed
through; the SDK does not convert the two contracts or infer token ownership.

Sources inspected 2026-11-06:
- https://docs.asaas.com/reference/pagar-uma-cobranca-com-cartao
- https://docs.asaas.com/reference/pagar-uma-cobranca-com-cartao-de-credito
- https://docs.asaas.com/reference/comece-por-aqui

These calls are not an idempotent scheduling mechanism. The built-in retry
middleware no longer retries either card payment endpoint, including timeout,
429 and 5xx responses. It also excludes creation of a payment containing card
or card-token fields, with or without the generated trailing slash. Read retries
retain their existing behavior. Custom HTTP clients must apply equivalent rules;
the SDK cannot replace caller-installed middleware.

After an inconclusive response, query the same charge and reconcile webhooks
before considering any further explicit attempt. A successful HTTP response is
not proof of settled funds. The caller must preserve invoice/customer/account/
environment identity, concurrency, authorization and reconciliation controls.
Do not log or persist card payloads, PAN, CVV, credentials or raw exceptions that
may retain request data. The tests use opaque synthetic tokens, no card numbers.

This change does not add a UI, token vault, automatic capture, installments,
3DS browser orchestration, accounting entries or retry scheduler. It does not
claim sandbox approval or production homologation. SDK integration in ARGWS CRM
is a separate change; its bundled version is not silently modified.
