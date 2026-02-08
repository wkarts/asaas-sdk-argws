# WebhookService

Acesso via fachada: `$asaas->webhook`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `listWebhooks` | GET | `/v3/webhooks` |
| `createNewWebhook` | POST | `/v3/webhooks` |
| `removeWebhook` | DELETE | `/v3/webhooks/{id}` |
| `retrieveASingleWebhook` | GET | `/v3/webhooks/{id}` |
| `updateExistingWebhook` | PUT | `/v3/webhooks/{id}` |
| `removeWebhookBackoff` | POST | `/v3/webhooks/{id}/removeBackoff` |

## Exemplo rápido

```php
$result = $asaas->webhook->listWebhooks(
    pathParams: [],
    query: [],
    headers: [],
    payload: null
);
```
