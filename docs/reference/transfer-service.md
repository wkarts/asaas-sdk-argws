# TransferService

Acesso via fachada: `$asaas->transfer`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `listTransfers` | GET | `/v3/transfers` |
| `transferToAnotherInstitutionAccountOrPixKey` | POST | `/v3/transfers` |
| `transferToAsaasAccount` | POST | `/v3/transfers/` |
| `retrieveASingleTransfer` | GET | `/v3/transfers/{id}` |
| `cancelATransfer` | DELETE | `/v3/transfers/{id}/cancel` |

## Exemplo rápido

```php
$result = $asaas->transfer->listTransfers(
    pathParams: [],
    query: [],
    headers: [],
    payload: null
);
```
