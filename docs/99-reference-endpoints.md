# 99 — Referência de endpoints (gerada do código)

Esta referência é gerada a partir de `src/Service/Generated/` e reflete **exatamente** o que existe na versão atual da SDK.

Padrão de assinatura (em todos os métodos):

- `pathParams: array $pathParams = []` (substitui `{param}` no path)

- `query: array $query = []` (filtros/paginação)

- `headers: array $headers = []` (headers extras)

- `payload: ?array $payload = null` (body JSON; use `null` em GET/DELETE)


## Índice por serviço

- [AccountDocumentService (`$asaas->accountDocument`)](reference/account-document-service.md)
- [AccountInfoService (`$asaas->accountInfo`)](reference/account-info-service.md)
- [AnticipationService (`$asaas->anticipation`)](reference/anticipation-service.md)
- [AutomaticPixService](reference/automatic-pix-service.md)
- [BillService (`$asaas->bill`)](reference/bill-service.md)
- [ChargebackService (`$asaas->chargeback`)](reference/chargeback-service.md)
- [CheckoutService (`$asaas->checkout`)](reference/checkout-service.md)
- [CreditBureauReportService (`$asaas->creditBureauReport`)](reference/credit-bureau-report-service.md)
- [CreditCardService (`$asaas->creditCard`)](reference/credit-card-service.md)
- [CustomerService (`$asaas->customer`)](reference/customer-service.md)
- [EscrowAccountService (`$asaas->escrowAccount`)](reference/escrow-account-service.md)
- [FinanceService (`$asaas->finance`)](reference/finance-service.md)
- [FinancialTransactionService (`$asaas->financialTransaction`)](reference/financial-transaction-service.md)
- [FiscalInfoService (`$asaas->fiscalInfo`)](reference/fiscal-info-service.md)
- [InstallmentService (`$asaas->installment`)](reference/installment-service.md)
- [InvoiceService (`$asaas->invoice`)](reference/invoice-service.md)
- [MobilePhoneRechargeService (`$asaas->mobilePhoneRecharge`)](reference/mobile-phone-recharge-service.md)
- [NotificationService (`$asaas->notification`)](reference/notification-service.md)
- [PaymentDocumentService (`$asaas->paymentDocument`)](reference/payment-document-service.md)
- [PaymentDunningService (`$asaas->paymentDunning`)](reference/payment-dunning-service.md)
- [PaymentLinkService (`$asaas->paymentLink`)](reference/payment-link-service.md)
- [PaymentRefundService (`$asaas->paymentRefund`)](reference/payment-refund-service.md)
- [PaymentService (`$asaas->payment`)](reference/payment-service.md)
- [PaymentSplitService (`$asaas->paymentSplit`)](reference/payment-split-service.md)
- [PaymentWithSummaryDataService (`$asaas->paymentWithSummaryData`)](reference/payment-with-summary-data-service.md)
- [PixService (`$asaas->pix`)](reference/pix-service.md)
- [PixTransactionService (`$asaas->pixTransaction`)](reference/pix-transaction-service.md)
- [RecurringPixService (`$asaas->recurringPix`)](reference/recurring-pix-service.md)
- [SandboxActionsService (`$asaas->sandboxActions`)](reference/sandbox-actions-service.md)
- [SubaccountService (`$asaas->subaccount`)](reference/subaccount-service.md)
- [SubscriptionService (`$asaas->subscription`)](reference/subscription-service.md)
- [TransferService (`$asaas->transfer`)](reference/transfer-service.md)
- [WebhookService (`$asaas->webhook`)](reference/webhook-service.md)
