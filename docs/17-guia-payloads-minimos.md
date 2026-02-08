# 20 - Guia de payloads mínimos (por recurso)

Este guia é **pragmático**: payloads mínimos (e “mínimos úteis”) para você implementar rápido em **ERP Laravel** e em **módulos Perfex CRM**, evitando tentativa/erro.

> Referências oficiais (Asaas):  
> - Cobrança (required fields `customer`, `billingType`, `value`, `dueDate`): https://docs.asaas.com/reference/create-new-payment-with-summary-data-in-response.md  
> - QR Code Pix estático (campos do payload): https://docs.asaas.com/reference/create-static-qrcode.md  
> - Criar Webhook (exemplo oficial + lista de eventos): https://docs.asaas.com/docs/criar-novo-webhook-pela-api  
> - Idempotência em Webhooks (ID único por evento): https://docs.asaas.com/docs/como-implementar-idempotencia-em-webhooks  
> - Assinaturas (webhooks são de cobrança e carregam `subscription`): https://docs.asaas.com/docs/subscriptions  

---

## 20.1 Customer (Cliente)

### Criar — **mínimo**
Campos que, na prática, você sempre usa para ter um cliente “amarrável” a cobrança:

```php
$customer = $asaas->customer->createNewCustomer(
    pathParams: [],
    query: [],
    headers: [],
    payload: [
        'name'    => 'João da Silva',
        'cpfCnpj' => '00586050000100',
        // opcional, mas recomendado:
        'email'   => 'financeiro@cliente.com.br',
        'phone'   => '75999999999',
    ],
);
```

### Criar — **mínimo útil para ERP/Perfex**
Inclua endereço quando você precisar emitir NFSe, validar CEP/cidade, ou reduzir risco de cadastro incompleto:

```php
payload: [
  'name'       => 'João da Silva',
  'cpfCnpj'    => '00586050000100',
  'email'      => 'financeiro@cliente.com.br',
  'mobilePhone'=> '75999999999',
  'postalCode' => '44000000',
  'address'    => 'Rua Exemplo',
  'addressNumber' => '123',
  'province'   => 'Centro',
]
```

### “Consulta antes de criar” (evita duplicar no ERP)
Padrão clássico: buscar por CPF/CNPJ e usar o **id** do retorno.

```php
$list = $asaas->customer->listCustomers(
    pathParams: [],
    query: ['cpfCnpj' => '00586050000100'],
    headers: [],
    payload: null,
);

$asaasCustomerId = $list['data'][0]['id'] ?? null;

if (!$asaasCustomerId) {
    $created = $asaas->customer->createNewCustomer(...);
    $asaasCustomerId = $created['id'];
}
```

---

## 20.2 Payment (Cobrança)

### Criar cobrança — **mínimo (BOLETO / PIX / CREDIT_CARD)**
A OpenAPI do endpoint “lean” expõe claramente os 4 campos obrigatórios: `customer`, `billingType`, `value`, `dueDate`. 

```php
$payment = $asaas->payment->createNewPayment(
  pathParams: [],
  query: [],
  headers: [],
  payload: [
    'customer'    => $asaasCustomerId,
    'billingType' => 'BOLETO', // ou PIX / CREDIT_CARD
    'value'       => 129.90,
    'dueDate'     => '2026-02-10',
  ],
);
```

### Criar boleto — **mínimo útil para ERP/Perfex**
- `description`: aparece em UI/boletos/relatórios
- `externalReference`: chave de idempotência sua (invoice_id / venda_id / titulo_id)

```php
$payment = $asaas->payment->createNewPayment(
  pathParams: [],
  query: [],
  headers: [],
  payload: [
    'customer'          => $asaasCustomerId,
    'billingType'       => 'BOLETO',
    'value'             => 129.90,
    'dueDate'           => '2026-02-10',
    'description'       => 'Mensalidade Escolar - Turma A - 02/2026',
    'externalReference' => 'ERP:invoice:287',
  ],
);
```

### Criar PIX (cobrança PIX) — **mínimo útil**
```php
$payment = $asaas->payment->createNewPayment(
  pathParams: [],
  query: [],
  headers: [],
  payload: [
    'customer'          => $asaasCustomerId,
    'billingType'       => 'PIX',
    'value'             => 129.90,
    'dueDate'           => '2026-02-10',
    'description'       => 'Mensalidade 02/2026',
    'externalReference' => 'ERP:invoice:287',
  ],
);
```

Depois, para obter o QRCode/payload do PIX:
```php
$qr = $asaas->payment->getQrCodeForPixPayments(
  pathParams: ['id' => $payment['id']],
  query: [],
  headers: [],
  payload: null,
);
```

### Criar cartão — 2 estratégias
1) **Redirecionar** para `invoiceUrl` (cliente digita cartão no Asaas).   
2) Enviar dados de cartão e titular no payload (quando aplicável).

> Dica prática (ERP/Perfex): para evitar lidar com PCI, comece pelo fluxo do `invoiceUrl`.

---

## 20.3 Subscription (Assinatura)

### Criar assinatura — **mínimo útil**
Assinatura “gera cobranças” automaticamente e você controla via webhooks de cobrança. 

```php
$subscription = $asaas->subscription->createNewSubscription(
  pathParams: [],
  query: [],
  headers: [],
  payload: [
    'customer'      => $asaasCustomerId,
    'billingType'   => 'BOLETO',    // ou PIX / CREDIT_CARD
    'value'         => 99.90,
    'nextDueDate'   => '2026-03-05',
    'cycle'         => 'MONTHLY',   // ex.: WEEKLY, MONTHLY, YEARLY
    'description'   => 'Plano Ouro - Mensal',
    'externalReference' => 'ERP:contract:991',
  ],
);
```

> Observação oficial: ao criar a assinatura, a primeira cobrança é gerada e você recebe `PAYMENT_CREATED` com o `subscription` id. 

---

## 20.4 Pix “puro” (QR Code estático)

Use quando você quer **receber** via Pix sem criar cobrança/cliente previamente (o Asaas cria automaticamente no recebimento, dependendo do fluxo). 

### Criar QR Code estático — **mínimo**
Campos principais do endpoint (OpenAPI) 

```php
$qr = $asaas->pix->createStaticQrCode(
  pathParams: [],
  query: [],
  headers: [],
  payload: [
    'addressKey' => 'b6295ee1-f054-47d1-9e90-ee57b74f60d9',
    // 'value' => 50.00, // opcional: se não informar, pagador escolhe o valor
    'format' => 'ALL',  // ALL | IMAGE | PAYLOAD
    'description' => 'Pagamento balcão',
    'externalReference' => 'ERP:cashdesk:2026-02-07:0001',
  ],
);
```

---

## 20.5 Webhook

### Criar webhook — **mínimo útil**
O guia oficial mostra exatamente o payload típico (nome, url, email, enabled, sendType, events...). 

```php
$webhook = $asaas->webhook->createNewWebhook(
  pathParams: [],
  query: [],
  headers: [],
  payload: [
    'name'      => 'ERP Webhooks',
    'url'       => 'https://seu-dominio.com/webhooks/asaas',
    'email'     => 'devops@seu-dominio.com.br',
    'enabled'   => true,
    'sendType'  => 'SEQUENTIALLY',
    'events'    => [
      'PAYMENT_CREATED',
      'PAYMENT_UPDATED',
      'PAYMENT_RECEIVED',
      'PAYMENT_OVERDUE',
      'PAYMENT_DELETED',
      'PAYMENT_REFUNDED',
    ],
  ],
);
```

---

## 20.6 Transfer (Transferência)

Para ERP/Perfex, o essencial é: **persistir IDs remotos** e “não duplicar” (externalReference quando existir no recurso; quando não existir, use sua própria tabela de dedupe).

> Sugestão de payload mínimo: mantenha no seu domínio “título/conta” e associe ao ID retornado pelo Asaas após criação.

---

## 20.7 Checklist rápido (por operação)

- Criar Customer: `name`, `cpfCnpj`, (email/phone recomendado)
- Criar Payment: `customer`, `billingType`, `value`, `dueDate` 
- Criar Subscription: `customer`, `billingType`, `value`, `nextDueDate`, `cycle`
- Criar Pix QR estático: `addressKey` (+ `value` opcional), `format` 
- Criar Webhook: `name`, `url`, `email`, `enabled`, `events` 
