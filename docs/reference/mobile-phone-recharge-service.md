# MobilePhoneRechargeService

Acesso via fachada: `$asaas->mobilePhoneRecharge`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `listMobileRecharges` | GET | `/v3/mobilePhoneRecharges` |
| `requestRecharge` | POST | `/v3/mobilePhoneRecharges` |
| `recoverASingleCellphoneRecharge` | GET | `/v3/mobilePhoneRecharges/{id}` |
| `cancelACellphoneRecharge` | POST | `/v3/mobilePhoneRecharges/{id}/cancel` |
| `searchForCellPhoneProvider` | GET | `/v3/mobilePhoneRecharges/{phoneNumber}/provider` |

## Exemplo rápido

```php
$result = $asaas->mobilePhoneRecharge->listMobileRecharges(
    pathParams: [],
    query: [],
    headers: [],
    payload: null
);
```
