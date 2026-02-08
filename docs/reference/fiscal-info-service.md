# FiscalInfoService

Acesso via fachada: `$asaas->fiscalInfo`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `retrieveTaxInformation` | GET | `/v3/fiscalInfo/` |
| `createAndUpdateTaxInformation` | POST | `/v3/fiscalInfo/` |
| `listFederalServiceCodes` | GET | `/v3/fiscalInfo/federalServiceCodes` |
| `listMunicipalConfigurations` | GET | `/v3/fiscalInfo/municipalOptions` |
| `configureInvoiceIssuingPortal` | POST | `/v3/fiscalInfo/nationalPortal` |
| `listNbsCodes` | GET | `/v3/fiscalInfo/nbsCodes` |
| `listOperationIndicatorCodes` | GET | `/v3/fiscalInfo/operationIndicatorCodes` |
| `listMunicipalServices` | GET | `/v3/fiscalInfo/services` |
| `listTaxClassificationCodes` | GET | `/v3/fiscalInfo/taxClassificationCodes` |
| `listTaxSituationCodes` | GET | `/v3/fiscalInfo/taxSituationCodes` |

## Exemplo rápido

```php
$result = $asaas->fiscalInfo->retrieveTaxInformation(
    pathParams: [],
    query: [],
    headers: [],
    payload: null
);
```
