# 05 — Serviços e endpoints

A fachada `AsaasSdk` expõe serviços como propriedades públicas. Abaixo, o inventário completo **com base no código atual**.

> **Observação importante:** apenas `PaymentService` possui métodos gerados no momento.
> Os demais serviços estão presentes, porém **sem métodos públicos** até que o OpenAPI gere as operações.

## Como descobrir métodos disponíveis

- Olhe os arquivos em `src/Service/Generated/`.
- O gerador cria métodos com a assinatura padrão:
  - `public function metodo(array $query = [], array $headers = []): mixed`
  - `public function metodo(array $payload, array $headers = []): mixed`

## Serviços (inventário completo)

### PaymentService (`$asaas->payment`)

Métodos públicos gerados:

| Método | HTTP | Path | Parâmetros | Retorno |
|---|---|---|---|---|
| `listPayments(array $query = [], array $headers = [])` | GET | `/payments` | `query`, `headers` | `mixed` (array) |
| `createPayment(array $payload, array $headers = [])` | POST | `/payments` | `payload`, `headers` | `mixed` (array) |

Exemplo:

```php
// Listar cobranças
$result = $asaas->payment->listPayments(['limit' => 10]);

// Criar cobrança
$payload = [
    'customer' => 'cus_123',
    'billingType' => 'BOLETO',
    'value' => 150.00,
    'dueDate' => '2025-01-20'
];
$result = $asaas->payment->createPayment($payload);
```

---

### Serviços sem métodos gerados (no momento)

Os serviços abaixo **existem**, mas **não possuem métodos públicos gerados** no código atual. Eles serão preenchidos quando você rodar a geração via OpenAPI.

- `$asaas->sandboxActions` (SandboxActionsService)
- `$asaas->paymentWithSummaryData` (PaymentWithSummaryDataService)
- `$asaas->creditCard` (CreditCardService)
- `$asaas->paymentRefund` (PaymentRefundService)
- `$asaas->paymentSplit` (PaymentSplitService)
- `$asaas->escrowAccount` (EscrowAccountService)
- `$asaas->paymentDocument` (PaymentDocumentService)
- `$asaas->customer` (CustomerService)
- `$asaas->notification` (NotificationService)
- `$asaas->installment` (InstallmentService)
- `$asaas->subscription` (SubscriptionService)
- `$asaas->pix` (PixService)
- `$asaas->pixTransaction` (PixTransactionService)
- `$asaas->anticipation` (AnticipationService)
- `$asaas->recurringPix` (RecurringPixService)
- `$asaas->paymentLink` (PaymentLinkService)
- `$asaas->checkout` (CheckoutService)
- `$asaas->transfer` (TransferService)
- `$asaas->paymentDunning` (PaymentDunningService)
- `$asaas->bill` (BillService)
- `$asaas->mobilePhoneRecharge` (MobilePhoneRechargeService)
- `$asaas->creditBureauReport` (CreditBureauReportService)
- `$asaas->financialTransaction` (FinancialTransactionService)
- `$asaas->finance` (FinanceService)
- `$asaas->accountInfo` (AccountInfoService)
- `$asaas->invoice` (InvoiceService)
- `$asaas->fiscalInfo` (FiscalInfoService)
- `$asaas->webhook` (WebhookService)
- `$asaas->subaccount` (SubaccountService)
- `$asaas->accountDocument` (AccountDocumentService)
- `$asaas->chargeback` (ChargebackService)

Exemplo de acesso (sem chamada pública disponível):

```php
$service = $asaas->customer;
// Sem métodos públicos gerados neste momento.
```

Para gerar métodos, consulte: **docs/13-geracao-openapi-e-paridade.md**.
