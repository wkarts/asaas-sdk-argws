# NotificationService

Acesso via fachada: `$asaas->notification`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `updateExistingNotificationsInBatch` | PUT | `/v3/notifications/batch` |
| `updateExistingNotification` | PUT | `/v3/notifications/{id}` |

## Exemplo rápido

```php
$result = $asaas->notification->updateExistingNotificationsInBatch(
    pathParams: [],
    query: [],
    headers: [],
    payload: [
        // ...
    ]
);
```
