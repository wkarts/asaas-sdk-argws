# RecurringPixService

Acesso via fachada: `$asaas->recurringPix`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `listRecurrences` | GET | `/v3/pix/transactions/recurrings` |
| `cancelARecurrenceItem` | POST | `/v3/pix/transactions/recurrings/items/{id}/cancel` |
| `retrieveASingleRecurrence` | GET | `/v3/pix/transactions/recurrings/{id}` |
| `cancelARecurrence` | POST | `/v3/pix/transactions/recurrings/{id}/cancel` |
| `listRecurrenceItems` | GET | `/v3/pix/transactions/recurrings/{id}/items` |

## Exemplo rápido

```php
$result = $asaas->recurringPix->listRecurrences(
    pathParams: [],
    query: [],
    headers: [],
    payload: null
);
```
