# InvoiceService

Acesso via fachada: `$asaas->invoice`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `listInvoices` | GET | `/v3/invoices` |
| `scheduleInvoice` | POST | `/v3/invoices` |
| `retrieveASingleInvoice` | GET | `/v3/invoices/{id}` |
| `updateInvoice` | PUT | `/v3/invoices/{id}` |
| `issueAnInvoice` | POST | `/v3/invoices/{id}/authorize` |
| `cancelAnInvoice` | POST | `/v3/invoices/{id}/cancel` |

## Exemplo rápido

```php
$result = $asaas->invoice->listInvoices(
    pathParams: [],
    query: [],
    headers: [],
    payload: null
);
```
