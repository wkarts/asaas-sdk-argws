# ChargebackService

Acesso via fachada: `$asaas->chargeback`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `listChargebacks` | GET | `/v3/chargebacks/` |
| `createAChargebackDispute` | POST | `/v3/chargebacks/{id}/dispute` |
| `retrieveASingleChargeback` | GET | `/v3/payments/{id}/chargeback` |

## Exemplo rápido

```php
$result = $asaas->chargeback->listChargebacks(
    pathParams: [],
    query: [],
    headers: [],
    payload: null
);
```
