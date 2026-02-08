# PaymentDocumentService

Acesso via fachada: `$asaas->paymentDocument`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `listDocumentsOfAPayment` | GET | `/v3/payments/{id}/documents` |
| `uploadPaymentDocuments` | POST | `/v3/payments/{id}/documents` |
| `deleteDocumentFromAPayment` | DELETE | `/v3/payments/{id}/documents/{documentId}` |
| `retrieveASingleDocumentOfAPayment` | GET | `/v3/payments/{id}/documents/{documentId}` |
| `updateSettingsOfADocumentOfAPayment` | PUT | `/v3/payments/{id}/documents/{documentId}` |

## Exemplo rápido

```php
$result = $asaas->paymentDocument->listDocumentsOfAPayment(
    pathParams: "id" => "id_aqui" ],
    query: [],
    headers: [],
    payload: null
);
```
