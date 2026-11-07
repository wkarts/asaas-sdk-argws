# AutomaticPixService

Acesso via fachada: `$asaas->automaticPix`.

| Método (SDK) | HTTP | Path |
|---|---|---|
| `listAutomaticPixAuthorizations` | GET | `/v3/pix/automatic/authorizations` |
| `createAnAutomaticPixAuthorization` | POST | `/v3/pix/automatic/authorizations` |
| `cancelAnAutomaticPixAuthorization` | DELETE | `/v3/pix/automatic/authorizations/{id}` |
| `retrieveASingleAutomaticPixAuthorization` | GET | `/v3/pix/automatic/authorizations/{id}` |
| `listAutomaticPixPaymentInstructions` | GET | `/v3/pix/automatic/paymentInstructions` |
| `retrieveASingleAutomaticPixPaymentInstruction` | GET | `/v3/pix/automatic/paymentInstructions/{id}` |
| `createAnAutomaticPixPaymentInstructionRetry` | POST | `/v3/pix/automatic/paymentInstructions/{id}/retries` |

## Exemplo rápido

```php
$result = $asaas->automaticPix->listAutomaticPixAuthorizations(
    query: ['customerId' => 'cus_123', 'status' => 'ACTIVE', 'offset' => 0, 'limit' => 100]
);

$instructions = $asaas->automaticPix->listAutomaticPixPaymentInstructions(
    query: ['authorizationId' => 'auth_123', 'customerId' => 'cus_123', 'status' => 'REFUSED']
);
```

Filtros de autorizações: `offset`, `limit`, `status`, `customerId`, `subscriptionId`.
Filtros de instruções: `authorizationId`, `customerId`, `paymentId`, `status`.
Estados da instrução: `AWAITING_REQUEST`, `SCHEDULED`, `DONE`, `CANCELLED`, `REFUSED`.
`DONE` não confirma a liquidação financeira da cobrança. Consulte o [guia de Pix Automático](../17-pagamentos-e-pix-automatico.md) e a [matriz de relatórios](../20-relatorios-financeiros.md).
