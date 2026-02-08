# SubaccountService

Acesso via fachada: `$asaas->subaccount`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `listSubaccounts` | GET | `/v3/accounts` |
| `createSubaccount` | POST | `/v3/accounts` |
| `retrieveASingleSubaccount` | GET | `/v3/accounts/{id}` |
| `listApiKeysForSubaccount` | GET | `/v3/accounts/{id}/accessTokens` |
| `createApiKeyForSubaccount` | POST | `/v3/accounts/{id}/accessTokens` |
| `deleteApiKeyForASubaccount` | DELETE | `/v3/accounts/{id}/accessTokens/{accessTokenId}` |
| `updateApiKeyForASubaccount` | PUT | `/v3/accounts/{id}/accessTokens/{accessTokenId}` |

## Exemplo rápido

```php
$result = $asaas->subaccount->listSubaccounts(
    pathParams: [],
    query: [],
    headers: [],
    payload: null
);
```
