# PixTransactionService

Acesso via fachada: `$asaas->pixTransaction`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `decodeAQrcodeForPayment` | POST | `/v3/pix/qrCodes/decode` |
| `payAQrcode` | POST | `/v3/pix/qrCodes/pay` |
| `listTransactions` | GET | `/v3/pix/transactions` |
| `retrieveASingleTransaction` | GET | `/v3/pix/transactions/{id}` |
| `cancelAScheduledTransaction` | POST | `/v3/pix/transactions/{id}/cancel` |

## Exemplo rápido

```php
$result = $asaas->pixTransaction->decodeAQrcodeForPayment(
    pathParams: [],
    query: [],
    headers: [],
    payload: [
        // ...
    ]
);
```
