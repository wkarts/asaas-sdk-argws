# CreditBureauReportService

Acesso via fachada: `$asaas->creditBureauReport`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `listCreditBureauReports` | GET | `/v3/creditBureauReport` |
| `makeConsultation` | POST | `/v3/creditBureauReport` |
| `retrieveACreditBureauReport` | GET | `/v3/creditBureauReport/{id}` |

## Exemplo rápido

```php
$result = $asaas->creditBureauReport->listCreditBureauReports(
    pathParams: [],
    query: [],
    headers: [],
    payload: null
);
```
