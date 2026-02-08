# PixService

Acesso via fachada: `$asaas->pix`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `listKeys` | GET | `/v3/pix/addressKeys` |
| `createAKey` | POST | `/v3/pix/addressKeys` |
| `removeKey` | DELETE | `/v3/pix/addressKeys/{id}` |
| `retrieveASingleKey` | GET | `/v3/pix/addressKeys/{id}` |
| `createStaticQrcode` | POST | `/v3/pix/qrCodes/static` |
| `deleteStaticQrcode` | DELETE | `/v3/pix/qrCodes/static/{id}` |
| `availableTokenBucketCheck` | GET | `/v3/pix/tokenBucket/addressKey` |

## Exemplo rápido

```php
$result = $asaas->pix->listKeys(
    pathParams: [],
    query: [],
    headers: [],
    payload: null
);
```
