# PaymentRefundService

Acesso via fachada: `$asaas->paymentRefund`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `refundBankSlip` | POST | `/v3/payments/{id}/bankSlip/refund` |
| `retrieveRefundsOfASinglePayment` | GET | `/v3/payments/{id}/refunds` |

## Exemplo rápido

```php
$result = $asaas->paymentRefund->refundBankSlip(
    pathParams: "id" => "id_aqui" ],
    query: [],
    headers: [],
    payload: [
        // ...
    ]
);
```
