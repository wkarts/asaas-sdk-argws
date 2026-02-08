# BillService

Acesso via fachada: `$asaas->bill`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `listBillPayments` | GET | `/v3/bill` |
| `createABillPayment` | POST | `/v3/bill` |
| `simulateABillPayment` | POST | `/v3/bill/simulate` |
| `retrieveASingleBillPayment` | GET | `/v3/bill/{id}` |
| `cancelBillPayment` | POST | `/v3/bill/{id}/cancel` |

## Exemplo rápido

```php
$result = $asaas->bill->listBillPayments(
    pathParams: [],
    query: [],
    headers: [],
    payload: null
);
```
