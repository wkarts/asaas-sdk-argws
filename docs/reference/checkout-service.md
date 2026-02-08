# CheckoutService

Acesso via fachada: `$asaas->checkout`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `createNewCheckout` | POST | `/v3/checkouts` |
| `cancelACheckout` | POST | `/v3/checkouts/{id}/cancel` |

## Exemplo rápido

```php
$result = $asaas->checkout->createNewCheckout(
    pathParams: [],
    query: [],
    headers: [],
    payload: [
        // ...
    ]
);
```
