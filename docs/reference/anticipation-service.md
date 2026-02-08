# AnticipationService

Acesso via fachada: `$asaas->anticipation`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `listAnticipations` | GET | `/v3/anticipations` |
| `requestAnticipation` | POST | `/v3/anticipations` |
| `retrieveStatusOfAutomaticAnticipation` | GET | `/v3/anticipations/configurations` |
| `updateStatusOfAutomaticAnticipation` | PUT | `/v3/anticipations/configurations` |
| `retrieveAnticipationLimits` | GET | `/v3/anticipations/limits` |
| `simulateAnticipation` | POST | `/v3/anticipations/simulate` |
| `retrieveASingleAnticipation` | GET | `/v3/anticipations/{id}` |
| `cancelAnticipation` | POST | `/v3/anticipations/{id}/cancel` |

## Exemplo rápido

```php
$result = $asaas->anticipation->listAnticipations(
    pathParams: [],
    query: [],
    headers: [],
    payload: null
);
```
