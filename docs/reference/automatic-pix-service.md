# AutomaticPixService

| Método (SDK) | HTTP | Path |
|---|---|---|
| `listAutomaticPixAuthorizations` | GET | `/v3/pix/automatic/authorizations` |
| `createAnAutomaticPixAuthorization` | POST | `/v3/pix/automatic/authorizations` |
| `cancelAnAutomaticPixAuthorization` | DELETE | `/v3/pix/automatic/authorizations/{id}` |
| `retrieveASingleAutomaticPixAuthorization` | GET | `/v3/pix/automatic/authorizations/{id}` |
| `listAutomaticPixPaymentInstructions` | GET | `/v3/pix/automatic/paymentInstructions` |
| `retrieveASingleAutomaticPixPaymentInstruction` | GET | `/v3/pix/automatic/paymentInstructions/{id}` |

## Exemplo rápido

```php
$result = $asaas->/* serviço */->listAutomaticPixAuthorizations(
    pathParams: [],
    query: [],
    headers: [],
    payload: null
);
```
