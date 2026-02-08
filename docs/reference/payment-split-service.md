# PaymentSplitService

Acesso via fachada: `$asaas->paymentSplit`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `listPaidSplits` | GET | `/v3/payments/splits/paid` |
| `retrieveASinglePaidSplit` | GET | `/v3/payments/splits/paid/{id}` |
| `listReceivedSplits` | GET | `/v3/payments/splits/received` |
| `retrieveASingleReceivedSplit` | GET | `/v3/payments/splits/received/{id}` |

## Exemplo rápido

```php
$result = $asaas->paymentSplit->listPaidSplits(
    pathParams: [],
    query: [],
    headers: [],
    payload: null
);
```
