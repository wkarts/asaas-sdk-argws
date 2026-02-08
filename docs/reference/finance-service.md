# FinanceService

Acesso via fachada: `$asaas->finance`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `retrieveAccountBalance` | GET | `/v3/finance/balance` |
| `billingStatistics` | GET | `/v3/finance/payment/statistics` |
| `retrieveSplitValues` | GET | `/v3/finance/split/statistics` |

## Exemplo rápido

```php
$result = $asaas->finance->retrieveAccountBalance(
    pathParams: [],
    query: [],
    headers: [],
    payload: null
);
```
