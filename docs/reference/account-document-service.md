# AccountDocumentService

Acesso via fachada: `$asaas->accountDocument`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `checkPendingDocuments` | GET | `/v3/myAccount/documents` |
| `removeSentDocument` | DELETE | `/v3/myAccount/documents/files/{id}` |
| `viewDocumentSent` | GET | `/v3/myAccount/documents/files/{id}` |
| `updateSentDocument` | POST | `/v3/myAccount/documents/files/{id}` |
| `sendDocuments` | POST | `/v3/myAccount/documents/{id}` |

## Exemplo rápido

```php
$result = $asaas->accountDocument->checkPendingDocuments(
    pathParams: [],
    query: [],
    headers: [],
    payload: null
);
```
