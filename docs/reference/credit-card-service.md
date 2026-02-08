# CreditCardService

Acesso via fachada: `$asaas->creditCard`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `creditCardTokenization` | POST | `/v3/creditCard/tokenizeCreditCard` |

## Exemplo rápido

```php
$result = $asaas->creditCard->creditCardTokenization(
    pathParams: [],
    query: [],
    headers: [],
    payload: [
        // ...
    ]
);
```
