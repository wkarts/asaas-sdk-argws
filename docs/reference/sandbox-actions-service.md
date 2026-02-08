# SandboxActionsService

Acesso via fachada: `$asaas->sandboxActions`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `confirmPayment` | POST | `/v3/sandbox/payment/{id}/confirm` |
| `forceExpire` | POST | `/v3/sandbox/payment/{id}/overdue` |

## Exemplo rápido

```php
$result = $asaas->sandboxActions->confirmPayment(
    pathParams: "id" => "id_aqui" ],
    query: [],
    headers: [],
    payload: [
        // ...
    ]
);
```
