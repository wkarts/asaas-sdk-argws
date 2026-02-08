# PaymentWithSummaryDataService

Acesso via fachada: `$asaas->paymentWithSummaryData`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `listPaymentsWithSummaryData` | GET | `/v3/lean/payments` |
| `createNewPaymentWithSummaryDataInResponse` | POST | `/v3/lean/payments` |
| `createNewPaymentWithCreditCardWithSummaryDataInResponse` | POST | `/v3/lean/payments/` |
| `deletePaymentWithSummaryData` | DELETE | `/v3/lean/payments/{id}` |
| `retrieveASinglePaymentWithSummaryData` | GET | `/v3/lean/payments/{id}` |
| `updateExistingPaymentWithSummaryDataInResponse` | PUT | `/v3/lean/payments/{id}` |
| `capturePaymentWithPreAuthorizationWithSummaryDataInResponse` | POST | `/v3/lean/payments/{id}/captureAuthorizedPayment` |
| `confirmCashReceiptWithSummaryDataInResponse` | POST | `/v3/lean/payments/{id}/receiveInCash` |
| `refundPaymentWithSummaryDataInResponse` | POST | `/v3/lean/payments/{id}/refund` |
| `restoreRemovedPaymentWithSummaryDataInResponse` | POST | `/v3/lean/payments/{id}/restore` |
| `undoCashReceiptConfirmationWithSummaryDataInResponse` | POST | `/v3/lean/payments/{id}/undoReceivedInCash` |

## Exemplo rápido

```php
$result = $asaas->paymentWithSummaryData->listPaymentsWithSummaryData(
    pathParams: [],
    query: [],
    headers: [],
    payload: null
);
```
