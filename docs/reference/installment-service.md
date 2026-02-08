# InstallmentService

Acesso via fachada: `$asaas->installment`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `listInstallments` | GET | `/v3/installments` |
| `createInstallment` | POST | `/v3/installments` |
| `createInstallmentWithCreditCard` | POST | `/v3/installments/` |
| `removeInstallment` | DELETE | `/v3/installments/{id}` |
| `retrieveASingleInstallment` | GET | `/v3/installments/{id}` |
| `generateInstallmentBooklet` | GET | `/v3/installments/{id}/paymentBook` |
| `cancelChargesOfAnInstallment` | DELETE | `/v3/installments/{id}/payments` |
| `listPaymentsOfAInstallment` | GET | `/v3/installments/{id}/payments` |
| `refundInstallment` | POST | `/v3/installments/{id}/refund` |
| `updateInstallmentSplits` | PUT | `/v3/installments/{id}/splits` |

## Exemplo rápido

```php
$result = $asaas->installment->listInstallments(
    pathParams: [],
    query: [],
    headers: [],
    payload: null
);
```
