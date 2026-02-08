# EscrowAccountService

Acesso via fachada: `$asaas->escrowAccount`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `retrieveDefaultEscrowAccountConfiguration` | GET | `/v3/accounts/escrow` |
| `createDefaultEscrowAccountConfigurationToAllSubaccounts` | POST | `/v3/accounts/escrow` |
| `reteriveEscrowAccountConfigurationForSubaccount` | GET | `/v3/accounts/{id}/escrow` |
| `saveOrUpdateEscrowAccountConfigurationForSubaccount` | POST | `/v3/accounts/{id}/escrow` |
| `finishPaymentEscrowInTheEscrowAccount` | POST | `/v3/escrow/{id}/finish` |
| `retrievePaymentEscrowInTheEscrowAccount` | GET | `/v3/payments/{id}/escrow` |

## Exemplo rápido

```php
$result = $asaas->escrowAccount->retrieveDefaultEscrowAccountConfiguration(
    pathParams: [],
    query: [],
    headers: [],
    payload: null
);
```
