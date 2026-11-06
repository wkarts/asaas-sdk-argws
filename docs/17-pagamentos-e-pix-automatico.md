# Pagamentos hospedados e Pix Automático

Exemplos de uso para uma aplicação já configurada. Executar métodos de escrita cria recursos; os testes da SDK usam somente mocks.

## Fatura com opção de cartão de débito

```php
$payment = $sdk->payment->createHostedCardPayment([
    'customer' => $customerId,
    'value' => 100.00,
    'dueDate' => '2027-01-15',
    'externalReference' => 'PEDIDO-123',
]);
// Disponibilize $payment['invoiceUrl'] ao cliente.
```

O helper aceita CREDIT_CARD (padrão) ou UNDEFINED, preserva campos adicionais e rejeita dados/tokens de cartão. O [guia oficial](https://docs.asaas.com/docs/cobrancas-via-cartao-de-credito) orienta utilizar invoiceUrl para débito; não envie DEBIT/DEBIT_CARD a payWithCard. `payAChargeWithCard` continua com o contrato card/cardToken e CREDIT/VOUCHER; `payAChargeWithCreditCard` usa creditCard/creditCardToken. Os métodos genéricos continuam disponíveis e não fazem validação completa do schema.

O [Checkout](https://docs.asaas.com/reference/criar-novo-checkout) é outro produto, com CREDIT_CARD/PIX. O retorno da criação e a navegação de callback não confirmam pagamento. Boleto e Pix comuns continuam em `payment->createNewPayment`, com BOLETO/PIX; o QR dinâmico é obtido por `getQrCodeForPixPayments`.

## Consentimento e ciclos de Pix Automático

```php
$authorization = $sdk->automaticPix->createAnAutomaticPixAuthorization(payload: [
    'customerId' => $customerId,
    'frequency' => 'MONTHLY',
    'contractId' => 'CONTRATO-123',
    'startDate' => '2027-01-15',
    'value' => 100.00,
    'paymentCreationMode' => 'SUBSCRIPTION',
    'retryPolicy' => 'NOT_ALLOWED',
    'immediateQrCode' => ['expirationSeconds' => 3600, 'originalValue' => 100.00],
]);
```

[Criação](https://docs.asaas.com/reference/criar-uma-autorizacao-pix-automatico): MANUAL é o padrão; SUBSCRIPTION requer value. Criar autorização fornece QR inicial, mas só o consentimento e o pagamento inicial permitem ativação. Não confunda recebimento inicial com autorização ativa.

[FAQ](https://docs.asaas.com/docs/faq-2): no modo MANUAL, cada cobrança usa pixAutomaticAuthorizationId de autorização ACTIVE. Split é permitido na cobrança manual, não no QR inicial. A conta precisa estar elegível; acompanhe mudanças de elegibilidade e recusas. Não implemente recorrência de cartão ou débito bancário como se fosse Pix Automático.

```php
$page = $sdk->automaticPix->listAutomaticPixAuthorizations(query: [
    'customerId' => $customerId, 'status' => 'ACTIVE', 'offset' => 0, 'limit' => 100,
]);
$instructions = $sdk->automaticPix->listAutomaticPixPaymentInstructions(query: [
    'authorizationId' => $authorizationId, 'status' => 'REFUSED',
]);
$instruction = $sdk->automaticPix->retrieveASingleAutomaticPixPaymentInstruction(['id' => $instructionId]);
```

A listagem de autorizações é paginada; a aplicação deve percorrer as páginas. Use webhooks para mudanças e consultas para conciliação. [DONE na instrução](https://docs.asaas.com/reference/listar-instrucoes-de-pagamento-pix-automatico) não substitui os eventos de confirmação financeira da cobrança. A SDK devolve o estado bruto e não liquida nada localmente.

## Retentativa financeira explícita

```php
$retry = $sdk->automaticPix->createAnAutomaticPixPaymentInstructionRetry(
    ['id' => $refusedInstructionId],
    payload: ['dueDate' => '2027-01-17'],
);
```

[Contrato](https://docs.asaas.com/reference/criar-retentativa-de-instrucao-de-pagamento-pix-automatico): exige instrução original recusada e política compatível. A API aceita a nova data conforme suas regras e mantém o valor. Não crie outra tentativa sem conhecer o resultado atual. Essa chamada é distinta do retry HTTP: o middleware só repete GET/HEAD/OPTIONS em falha de transporte, 429 ou 5xx, com limite e backoff. Escritas nunca são repetidas automaticamente, mesmo com header de idempotência. Um cliente HTTP customizado pode ter middleware próprio: cabe ao integrador manter essa política.

Webhooks devem ter token validado e processamento idempotente pelo ID do evento. Não registrar payloads com cartão, tokens ou chaves. Nenhuma credencial real é necessária para executar os testes unitários.
