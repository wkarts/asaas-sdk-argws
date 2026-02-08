# PaymentLinkService

Acesso via fachada: `$asaas->paymentLink`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `listPaymentsLinks` | GET | `/v3/paymentLinks` |
| `createAPaymentsLink` | POST | `/v3/paymentLinks` |
| `removeAPaymentsLink` | DELETE | `/v3/paymentLinks/{id}` |
| `retrieveASinglePaymentsLink` | GET | `/v3/paymentLinks/{id}` |
| `updateAPaymentsLink` | PUT | `/v3/paymentLinks/{id}` |
| `listImagesFromAPaymentsLink` | GET | `/v3/paymentLinks/{id}/images` |
| `addAnImageToAPaymentsLink` | POST | `/v3/paymentLinks/{id}/images` |
| `restoreAPaymentsLink` | POST | `/v3/paymentLinks/{id}/restore` |
| `removeAnImageFromPaymentsLink` | DELETE | `/v3/paymentLinks/{paymentLinkId}/images/{imageId}` |
| `retrieveASinglePaymentsLinkImage` | GET | `/v3/paymentLinks/{paymentLinkId}/images/{imageId}` |
| `setPaymentsLinkMainImage` | PUT | `/v3/paymentLinks/{paymentLinkId}/images/{imageId}/setAsMain` |

## Exemplo rápido

```php
$result = $asaas->paymentLink->listPaymentsLinks(
    pathParams: [],
    query: [],
    headers: [],
    payload: null
);
```
