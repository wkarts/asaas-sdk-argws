# 00 — Introdução

> **NÃO OFICIAL** — Não afiliada ao Asaas.  
> Baseada na SDK Java oficial e na documentação pública/OpenAPI.  
> Asaas é marca de seus respectivos proprietários.

Esta SDK PHP é comunitária e tem como objetivo facilitar a integração com a API do Asaas, priorizando:

- Compatibilidade com PHP 8.1+
- Geração automática via OpenAPI
- Paridade com a SDK Java oficial (quando possível)
- Estrutura simples: `AsaasSdk` como fachada e serviços por recurso

## O que existe hoje (inventário real)

Classes públicas principais:

- **Asaas\Sdk\AsaasSdk** (fachada principal)
- **Asaas\Sdk\Config\AsaasConfig** (configuração)
- **Asaas\Sdk\Http\Environment** (Sandbox/Production)
- **Asaas\Sdk\Http\Client** (HTTP interno)
- **Asaas\Sdk\Exception\ApiException**
- **Asaas\Sdk\Exception\TransportException**
- **Asaas\Sdk\Exception\ValidationException**
- **Asaas\Sdk\Util\Serializer**
- **Asaas\Sdk\Util\Query**
- **Asaas\Sdk\Generator\OpenApiBuilder / SdkGenerator / ParityVerifier** (uso em geração)
- **Asaas\Sdk\Model\Contracts\ArraySerializable** (interface de DTOs)

Serviços expostos em `AsaasSdk`:

- payment, sandboxActions, paymentWithSummaryData, creditCard, paymentRefund, paymentSplit,
  escrowAccount, paymentDocument, customer, notification, installment, subscription, pix,
  pixTransaction, anticipation, recurringPix, paymentLink, checkout, transfer, paymentDunning,
  bill, mobilePhoneRecharge, creditBureauReport, financialTransaction, finance, accountInfo,
  invoice, fiscalInfo, webhook, subaccount, accountDocument, chargeback

> **Importante:** no estado atual do código, **apenas `PaymentService` possui métodos gerados**
> (`listPayments` e `createPayment`). Os demais serviços estão presentes como classes vazias
> aguardando geração a partir do OpenAPI.
