<?php

declare(strict_types=1);

namespace Asaas\Sdk;

use Asaas\Sdk\Config\AsaasConfig;
use Asaas\Sdk\Http\Client;
use Asaas\Sdk\Http\Environment;
use Asaas\Sdk\Service\AccountDocumentService;
use Asaas\Sdk\Service\AccountInfoService;
use Asaas\Sdk\Service\AnticipationService;
use Asaas\Sdk\Service\BillService;
use Asaas\Sdk\Service\ChargebackService;
use Asaas\Sdk\Service\CheckoutService;
use Asaas\Sdk\Service\CreditBureauReportService;
use Asaas\Sdk\Service\CreditCardService;
use Asaas\Sdk\Service\CustomerService;
use Asaas\Sdk\Service\EscrowAccountService;
use Asaas\Sdk\Service\FinanceService;
use Asaas\Sdk\Service\FinancialTransactionService;
use Asaas\Sdk\Service\FiscalInfoService;
use Asaas\Sdk\Service\InstallmentService;
use Asaas\Sdk\Service\InvoiceService;
use Asaas\Sdk\Service\MobilePhoneRechargeService;
use Asaas\Sdk\Service\NotificationService;
use Asaas\Sdk\Service\PaymentDocumentService;
use Asaas\Sdk\Service\PaymentDunningService;
use Asaas\Sdk\Service\PaymentLinkService;
use Asaas\Sdk\Service\PaymentRefundService;
use Asaas\Sdk\Service\PaymentService;
use Asaas\Sdk\Service\PaymentSplitService;
use Asaas\Sdk\Service\PaymentWithSummaryDataService;
use Asaas\Sdk\Service\PixService;
use Asaas\Sdk\Service\PixTransactionService;
use Asaas\Sdk\Service\RecurringPixService;
use Asaas\Sdk\Service\SandboxActionsService;
use Asaas\Sdk\Service\SubaccountService;
use Asaas\Sdk\Service\SubscriptionService;
use Asaas\Sdk\Service\TransferService;
use Asaas\Sdk\Service\WebhookService;

final class AsaasSdk
{
    public PaymentService $payment;
    public SandboxActionsService $sandboxActions;
    public PaymentWithSummaryDataService $paymentWithSummaryData;
    public CreditCardService $creditCard;
    public PaymentRefundService $paymentRefund;
    public PaymentSplitService $paymentSplit;
    public EscrowAccountService $escrowAccount;
    public PaymentDocumentService $paymentDocument;
    public CustomerService $customer;
    public NotificationService $notification;
    public InstallmentService $installment;
    public SubscriptionService $subscription;
    public PixService $pix;
    public PixTransactionService $pixTransaction;
    public AnticipationService $anticipation;
    public RecurringPixService $recurringPix;
    public PaymentLinkService $paymentLink;
    public CheckoutService $checkout;
    public TransferService $transfer;
    public PaymentDunningService $paymentDunning;
    public BillService $bill;
    public MobilePhoneRechargeService $mobilePhoneRecharge;
    public CreditBureauReportService $creditBureauReport;
    public FinancialTransactionService $financialTransaction;
    public FinanceService $finance;
    public AccountInfoService $accountInfo;
    public InvoiceService $invoice;
    public FiscalInfoService $fiscalInfo;
    public WebhookService $webhook;
    public SubaccountService $subaccount;
    public AccountDocumentService $accountDocument;
    public ChargebackService $chargeback;

    private Client $client;

    public function __construct(private AsaasConfig $config)
    {
        $this->client = new Client(
            $config->apiKey,
            $config->environment,
            $config->appName,
            $config->timeout,
            $config->connectTimeout,
            $config->logger,
            $config->httpClient
        );

        $this->payment = new PaymentService($this->client);
        $this->sandboxActions = new SandboxActionsService($this->client);
        $this->paymentWithSummaryData = new PaymentWithSummaryDataService($this->client);
        $this->creditCard = new CreditCardService($this->client);
        $this->paymentRefund = new PaymentRefundService($this->client);
        $this->paymentSplit = new PaymentSplitService($this->client);
        $this->escrowAccount = new EscrowAccountService($this->client);
        $this->paymentDocument = new PaymentDocumentService($this->client);
        $this->customer = new CustomerService($this->client);
        $this->notification = new NotificationService($this->client);
        $this->installment = new InstallmentService($this->client);
        $this->subscription = new SubscriptionService($this->client);
        $this->pix = new PixService($this->client);
        $this->pixTransaction = new PixTransactionService($this->client);
        $this->anticipation = new AnticipationService($this->client);
        $this->recurringPix = new RecurringPixService($this->client);
        $this->paymentLink = new PaymentLinkService($this->client);
        $this->checkout = new CheckoutService($this->client);
        $this->transfer = new TransferService($this->client);
        $this->paymentDunning = new PaymentDunningService($this->client);
        $this->bill = new BillService($this->client);
        $this->mobilePhoneRecharge = new MobilePhoneRechargeService($this->client);
        $this->creditBureauReport = new CreditBureauReportService($this->client);
        $this->financialTransaction = new FinancialTransactionService($this->client);
        $this->finance = new FinanceService($this->client);
        $this->accountInfo = new AccountInfoService($this->client);
        $this->invoice = new InvoiceService($this->client);
        $this->fiscalInfo = new FiscalInfoService($this->client);
        $this->webhook = new WebhookService($this->client);
        $this->subaccount = new SubaccountService($this->client);
        $this->accountDocument = new AccountDocumentService($this->client);
        $this->chargeback = new ChargebackService($this->client);
    }

    public function setApiKey(string $apiKey): void
    {
        $this->config->apiKey = $apiKey;
        $this->client->setApiKey($apiKey);
    }

    public function setEnvironment(Environment $environment): void
    {
        $this->config->environment = $environment;
        $this->client->setEnvironment($environment);
    }
}
