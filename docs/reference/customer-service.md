# CustomerService

Acesso via fachada: `$asaas->customer`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `listCustomers` | GET | `/v3/customers` |
| `createNewCustomer` | POST | `/v3/customers` |
| `removeCustomer` | DELETE | `/v3/customers/{id}` |
| `retrieveASingleCustomer` | GET | `/v3/customers/{id}` |
| `updateExistingCustomer` | PUT | `/v3/customers/{id}` |
| `retrieveNotificationsFromACustomer` | GET | `/v3/customers/{id}/notifications` |
| `restoreRemovedCustomer` | POST | `/v3/customers/{id}/restore` |

## Exemplo rápido

```php
$result = $asaas->customer->listCustomers(
    pathParams: [],
    query: [],
    headers: [],
    payload: null
);
```
