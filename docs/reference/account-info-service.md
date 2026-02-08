# AccountInfoService

Acesso via fachada: `$asaas->accountInfo`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `deleteWhiteLabelSubaccount` | DELETE | `/v3/myAccount/` |
| `retrieveAsaasAccountNumber` | GET | `/v3/myAccount/accountNumber` |
| `retrieveBusinessData` | GET | `/v3/myAccount/commercialInfo/` |
| `updateBusinessData` | POST | `/v3/myAccount/commercialInfo/` |
| `retrieveAccountFees` | GET | `/v3/myAccount/fees/` |
| `retrievePersonalizationSettings` | GET | `/v3/myAccount/paymentCheckoutConfig/` |
| `savePaymentCheckoutPersonalization` | POST | `/v3/myAccount/paymentCheckoutConfig/` |
| `checkAccountRegistrationStatus` | GET | `/v3/myAccount/status/` |
| `retrieveWalletid` | GET | `/v3/wallets/` |

## Exemplo rápido

```php
$result = $asaas->accountInfo->deleteWhiteLabelSubaccount(
    pathParams: [],
    query: [],
    headers: [],
    payload: null
);
```
