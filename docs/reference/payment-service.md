# PaymentService

Acesso via fachada: `$asaas->payment`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `listPayments` | GET | `/v3/payments` |
| `createNewPayment` | POST | `/v3/payments` |
| `createNewPaymentWithCreditCard` | POST | `/v3/payments/` |
| `recoveringPaymentLimits` | GET | `/v3/payments/limits` |
| `salesSimulator` | POST | `/v3/payments/simulate` |
| `deletePayment` | DELETE | `/v3/payments/{id}` |
| `retrieveASinglePayment` | GET | `/v3/payments/{id}` |
| `updateExistingPayment` | PUT | `/v3/payments/{id}` |
| `retrievePaymentBillingInformation` | GET | `/v3/payments/{id}/billingInfo` |
| `capturePaymentWithPreAuthorization` | POST | `/v3/payments/{id}/captureAuthorizedPayment` |
| `getDigitableBillLine` | GET | `/v3/payments/{id}/identificationField` |
| `payAChargeWithCreditCard` | POST | `/v3/payments/{id}/payWithCreditCard` |
| `getQrCodeForPixPayments` | GET | `/v3/payments/{id}/pixQrCode` |
| `confirmCashReceipt` | POST | `/v3/payments/{id}/receiveInCash` |
| `refundPayment` | POST | `/v3/payments/{id}/refund` |
| `restoreRemovedPayment` | POST | `/v3/payments/{id}/restore` |
| `retrieveStatusOfAPayment` | GET | `/v3/payments/{id}/status` |
| `undoCashReceiptConfirmation` | POST | `/v3/payments/{id}/undoReceivedInCash` |
| `paymentViewingInformation` | GET | `/v3/payments/{id}/viewingInfo` |

## Exemplo rápido

```php
$result = $asaas->payment->listPayments(
    pathParams: [],
    query: [],
    headers: [],
    payload: null
);
```
