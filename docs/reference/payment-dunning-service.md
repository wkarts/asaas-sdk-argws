# PaymentDunningService

Acesso via fachada: `$asaas->paymentDunning`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `listPaymentDunnings` | GET | `/v3/paymentDunnings` |
| `createAPaymentDunning` | POST | `/v3/paymentDunnings` |
| `listPaymentsAvailableForPaymentDunning` | GET | `/v3/paymentDunnings/paymentsAvailableForDunning` |
| `simulateAPaymentDunning` | POST | `/v3/paymentDunnings/simulate` |
| `recoverASinglePaymentDunning` | GET | `/v3/paymentDunnings/{id}` |
| `cancelPaymentDunning` | POST | `/v3/paymentDunnings/{id}/cancel` |
| `resendDocuments` | POST | `/v3/paymentDunnings/{id}/documents` |
| `eventHistoryLists` | GET | `/v3/paymentDunnings/{id}/history` |
| `listPaymentsReceived` | GET | `/v3/paymentDunnings/{id}/partialPayments` |

## Exemplo rápido

```php
$result = $asaas->paymentDunning->listPaymentDunnings(
    pathParams: [],
    query: [],
    headers: [],
    payload: null
);
```
